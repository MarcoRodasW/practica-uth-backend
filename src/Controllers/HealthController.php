<?php

namespace UthApi\Controllers;

use PDO;
use UthApi\Config\Database;
use UthApi\Utils\Response;
use PDOException;

class HealthController
{
    public function checkDatabase(): void
    {
        try {
            $db = Database::getConnection();

            // Test basic connection
            $stmt = $db->query("SELECT 1 as test");
            $result = $stmt->fetch();

            if ($result && $result['test'] == 1) {
                // Test if our tables exist
                $tablesCheck = $this->checkTables($db);

                Response::success([
                    'database_connection' => 'OK',
                    'tables_status' => $tablesCheck,
                    'timestamp' => date('Y-m-d H:i:s')
                ], 'Database connection successful');
            } else {
                Response::error('Database query test failed', 500);
            }
        } catch (PDOException $e) {
            Response::error('Database connection failed', 500, [
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Response::error('Unexpected error occurred', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function healthCheck(): void
    {
        try {
            $db = Database::getConnection();

            // Basic health information
            $health = [
                'status' => 'OK',
                'timestamp' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'database_connection' => 'OK'
            ];

            // Test database connection
            $stmt = $db->query("SELECT VERSION() as mysql_version");
            $result = $stmt->fetch();

            if ($result) {
                $health['mysql_version'] = $result['mysql_version'];
            }

            // Check tables
            $tablesCheck = $this->checkTables($db);
            $health['tables'] = $tablesCheck;

            // Check stored procedures
            $proceduresCheck = $this->checkStoredProcedures($db);
            $health['stored_procedures'] = $proceduresCheck;

            Response::success($health, 'System health check passed');
        } catch (PDOException $e) {
            Response::error('Health check failed - Database error', 500, [
                'database_error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Response::error('Health check failed', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function checkTables(\PDO $db): array
    {
        $tables = ['users', 'todos'];
        $tableStatus = [];

        foreach ($tables as $table) {
            try {
                // Use SHOW TABLES without parameters for MariaDB compatibility
                $stmt = $db->query("SHOW TABLES");
                $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $exists = in_array($table, $allTables);

                if ($exists) {
                    // Get row count
                    $countStmt = $db->query("SELECT COUNT(*) as count FROM `{$table}`");
                    $count = $countStmt->fetch()['count'];

                    // Get table structure info
                    $structureStmt = $db->query("DESCRIBE `{$table}`");
                    $columns = $structureStmt->fetchAll();

                    $tableStatus[$table] = [
                        'exists' => true,
                        'row_count' => (int)$count,
                        'column_count' => count($columns)
                    ];
                } else {
                    $tableStatus[$table] = [
                        'exists' => false,
                        'row_count' => 0,
                        'column_count' => 0
                    ];
                }
            } catch (PDOException $e) {
                $tableStatus[$table] = [
                    'exists' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $tableStatus;
    }
    private function checkStoredProcedures(\PDO $db): array
    {
        $procedures = [
            'CreateUser',
            'GetUserByEmail',
            'UpdateUserProfile',
            'CreateTodo',
            'GetUserTodos',
            'GetTodoById',
            'UpdateTodo',
            'DeleteTodo'
        ];

        $procedureStatus = [];

        foreach ($procedures as $procedure) {
            try {
                $stmt = $db->prepare("
                    SELECT COUNT(*) as count 
                    FROM information_schema.ROUTINES 
                    WHERE ROUTINE_SCHEMA = DATABASE() 
                    AND ROUTINE_NAME = ? 
                    AND ROUTINE_TYPE = 'PROCEDURE'
                ");
                $stmt->execute([$procedure]);
                $exists = $stmt->fetch()['count'] > 0;

                $procedureStatus[$procedure] = $exists;
            } catch (PDOException $e) {
                $procedureStatus[$procedure] = false;
            }
        }

        return $procedureStatus;
    }
}
