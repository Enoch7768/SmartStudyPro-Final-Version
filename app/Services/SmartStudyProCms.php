<?php

require_once __DIR__ . '/../../config.php';

final class SmartStudyProCms {
    private static ?PDO $db = null;

    public static function db(): PDO {
        if (self::$db instanceof PDO) {
            return self::$db;
        }

        $dir = __DIR__ . '/../../database';
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        $db = new PDO('sqlite:' . $dir . '/cms.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA foreign_keys = ON');
        $db->exec('PRAGMA busy_timeout = 5000');
        $db->exec('PRAGMA journal_mode = WAL');

        $db->exec("CREATE TABLE IF NOT EXISTS cms_documents (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            collection TEXT NOT NULL,
            document_id TEXT NOT NULL,
            data_json TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(collection, document_id)
        )");

        $db->exec("CREATE INDEX IF NOT EXISTS idx_cms_documents_collection ON cms_documents(collection)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_cms_documents_updated ON cms_documents(updated_at)");

        self::$db = $db;
        return $db;
    }

    public static function items(string $collection, array $options = []): array {
        $rows = self::db()->prepare('SELECT document_id, data_json FROM cms_documents WHERE collection = :collection ORDER BY id ASC');
        $rows->execute([':collection' => $collection]);
        $items = [];

        foreach ($rows->fetchAll() as $row) {
            $data = json_decode($row['data_json'], true);
            if (!is_array($data)) {
                continue;
            }
            $data['_id'] = $data['_id'] ?? $row['document_id'];
            if (self::matches($data, $options['filter'] ?? [])) {
                $items[] = $data;
            }
        }

        if (!empty($options['sort']) && is_array($options['sort'])) {
            foreach (array_reverse($options['sort'], true) as $field => $direction) {
                usort($items, static function (array $a, array $b) use ($field, $direction): int {
                    $av = $a[$field] ?? null;
                    $bv = $b[$field] ?? null;
                    $cmp = is_numeric($av) && is_numeric($bv) ? ((float) $av <=> (float) $bv) : strnatcasecmp((string) $av, (string) $bv);
                    return ((int) $direction) < 0 ? -$cmp : $cmp;
                });
            }
        }

        if (isset($options['limit']) && (int) $options['limit'] > 0) {
            $items = array_slice($items, 0, (int) $options['limit']);
        }

        return $items;
    }

    public static function item(string $collection, ?array $filter = null): ?array {
        $items = self::items($collection, ['filter' => $filter ?? []]);
        return $items[0] ?? null;
    }

    public static function findOne(string $collection, array $filter = []): ?array {
        return self::item($collection, $filter);
    }

    public static function save(string $collection, array $data): string {
        $id = trim((string) ($data['_id'] ?? ''));
        if ($id === '') {
            $id = bin2hex(random_bytes(12));
        }
        $data['_id'] = $id;

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $stmt = self::db()->prepare("INSERT INTO cms_documents (collection, document_id, data_json)
            VALUES (:collection, :document_id, :data_json)
            ON CONFLICT(collection, document_id) DO UPDATE SET data_json = excluded.data_json, updated_at = CURRENT_TIMESTAMP");
        $stmt->execute([
            ':collection' => $collection,
            ':document_id' => $id,
            ':data_json' => $json,
        ]);

        return $id;
    }

    public static function delete(string $collection, string $id): bool {
        $stmt = self::db()->prepare('DELETE FROM cms_documents WHERE collection = :collection AND document_id = :document_id');
        $stmt->execute([':collection' => $collection, ':document_id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function collections(): array {
        $stmt = self::db()->query('SELECT DISTINCT collection FROM cms_documents ORDER BY collection');
        return array_values(array_map(static fn(array $row): string => $row['collection'], $stmt->fetchAll()));
    }

    public static function count(string $collection): int {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM cms_documents WHERE collection = :collection');
        $stmt->execute([':collection' => $collection]);
        return (int) $stmt->fetchColumn();
    }

    private static function matches(array $data, array $filter): bool {
        foreach ($filter as $field => $expected) {
            $actual = $data[$field] ?? null;
            if (is_array($expected)) {
                if (is_array($actual)) {
                    if ($actual != $expected) {
                        return false;
                    }
                } elseif ((string) $actual !== (string) ($expected[0] ?? '')) {
                    return false;
                }
            } elseif ((string) $actual !== (string) $expected) {
                return false;
            }
        }
        return true;
    }
}

function cms_items(string $collection, array $options = []): array {
    return SmartStudyProCms::items($collection, $options);
}

function cms_item(string $collection, ?array $filter = null): ?array {
    return SmartStudyProCms::item($collection, $filter);
}

function cms_find_one(string $collection, array $filter = []): ?array {
    return SmartStudyProCms::findOne($collection, $filter);
}
