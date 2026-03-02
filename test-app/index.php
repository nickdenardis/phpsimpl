<?php
// Must load config FIRST before any HTML output (for session_start)
require_once 'config.php';

use Simpl\DB;
use Simpl\Validate;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHPSimpl Test Application</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        .status {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .badge.active {
            background: #d4edda;
            color: #155724;
        }
        .badge.inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .info-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        .info-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }
        .code {
            background: #272822;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
            margin: 15px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 PHPSimpl Test Application</h1>
        <p class="subtitle">Testing database connectivity and framework functionality</p>

<?php
// Test database connection
$db = new DB();
$db->DbConnect(); // Force connection (DB uses lazy loading)
?>

        <?php if ($db->IsConnected()): ?>
            <div class="status success">
                ✓ Database connection successful!
            </div>
        <?php else: ?>
            <div class="status error">
                ✗ Database connection failed. Please check your configuration.
            </div>
        <?php endif; ?>

        <div class="section">
            <h2>📊 Database Information</h2>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Database Name</div>
                    <div class="info-value"><?php echo h($db->getDatabase()); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">PHP Version</div>
                    <div class="info-value"><?php echo phpversion(); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Query Count</div>
                    <div class="info-value"><?php echo $db->query_count; ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Server</div>
                    <div class="info-value"><?php echo h(DBHOST); ?></div>
                </div>
            </div>
        </div>

        <?php if ($db->IsConnected()): ?>
            <div class="section">
                <h2>👥 Sample Users</h2>
                <?php
                $result = $db->Query("SELECT * FROM test_users ORDER BY id LIMIT 10");
                if ($db->NumRows($result) > 0):
                ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $db->FetchArray($result)): ?>
                                <tr>
                                    <td><?php echo h($row['id']); ?></td>
                                    <td><?php echo h($row['name']); ?></td>
                                    <td><?php echo h($row['email']); ?></td>
                                    <td><?php echo h(isset($row['phone']) ? $row['phone'] : 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo h($row['status']); ?>">
                                            <?php echo h($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo h($row['created_at']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No users found. Did you run the database.sql script?</p>
                <?php endif; ?>
            </div>

            <div class="section">
                <h2>📝 Example: Validation</h2>
                <?php
                // Use Validate class directly (Form class has a bug with Field constructor)
                $validate = new Validate();
                
                $testData = array(
                    'email' => 'test@example.com',
                    'alphanum' => 'Test123',
                    'url' => 'https://example.com',
                    'bad_email' => 'not-an-email'
                );
                ?>
                
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-label">Email: <?php echo h($testData['email']); ?></div>
                        <div class="info-value"><?php echo $validate->Check('email', $testData['email']) ? '✓ Valid' : '✗ Invalid'; ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Alphanum: <?php echo h($testData['alphanum']); ?></div>
                        <div class="info-value"><?php echo $validate->Check('alphanum', $testData['alphanum']) ? '✓ Valid' : '✗ Invalid'; ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">URL: <?php echo h($testData['url']); ?></div>
                        <div class="info-value"><?php echo $validate->Check('url', $testData['url']) ? '✓ Valid' : '✗ Invalid'; ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Bad Email: <?php echo h($testData['bad_email']); ?></div>
                        <div class="info-value"><?php echo $validate->Check('email', $testData['bad_email']) ? '✓ Valid' : '✗ Invalid'; ?></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>🔍 Example: Database Query</h2>
                <p>Here's how PHPSimpl executes a simple query:</p>
                <div class="code">$db = new DB();<br>
$result = $db->Query("SELECT * FROM test_users");<br>
while ($row = $db->FetchArray($result)) {<br>
&nbsp;&nbsp;echo $row['name'];<br>
}</div>
                <p style="margin-top: 15px;">
                    <strong>Queries executed so far:</strong> <?php echo $db->query_count; ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="section">
            <h2>🧪 Run Tests</h2>
            <p>To run the automated test suite:</p>
            <div class="code">
                # Start MariaDB<br>
                docker-compose up -d<br><br>
                # Run tests<br>
                composer test
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666;">
            <p>PHPSimpl Framework Test Application</p>
            <p style="font-size: 0.9rem; margin-top: 5px;">
                Testing backward compatibility during modernization
            </p>
        </div>
    </div>
</body>
</html>
