<!DOCTYPE html>
<html>
<head>
    <title>Test APP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all"/>
    <link rel="stylesheet" type="text/css" href="/bootstrap/css/bootstrap.min.css">
    <script type="text/javascript" src="/bootstrap/js/bootstrap.min.js"></script>
</head>
<?php 
    # ini_set('display_errors', 'Off'); 

    $db_err = FALSE;

    $env = getenv();
    $db_host = $env['DB_HOST'] ?? 'localhost';
    $db_port = $env['DB_PORT'] ?? '5432';
    $db_base = $env['DB_BASE'] ?? 'postgres';
    $db_user = $env['DB_USER'] ?? 'postgres';
    $db_pass = $env['DB_PASS'] ?? 'example';

    function contrast_color($hex)
    {
        $hex = trim($hex, ' #');
    
        $size = strlen($hex);	
        if ($size == 3) {
            $parts = str_split($hex, 1);
            $hex = '';
            foreach ($parts as $row) {
                $hex .= $row . $row;
            }		
        }
     
        $dec = hexdec($hex);
        $rgb = array(
            0xFF & ($dec >> 0x10),
            0xFF & ($dec >> 0x8),
            0xFF & $dec
        );
    
        $contrast = (round($rgb[0] * 299) + round($rgb[1] * 587) + round($rgb[2] * 114)) / 1000;
        return ($contrast >= 125) ? '#000' : '#fff';
    }

    function get_tables($db_conn, $db_base) {
        $result = [
            'status' => true,
            'error' => "",
            'data' => [],
        ];
        try {
            $res = $db_conn->query("SELECT * FROM pg_catalog.pg_tables ORDER BY tablename");
            if ($res !== false) {
                foreach ($res as $row) {
                    if ($row['schemaname'] == "public") {
                        $item = [
                            'table' => $row['tablename'],
                            'schema' => $row['schemaname'],
                            'owner' => $row['tableowner'],
                        ];
                        $result['data'][$row['tablename']] = $item;
                    }
                }
            }
        } catch (PDOException $ex) {
            $result['status'] = false;
            $result['error'] = $ex->getMessage();
        }

        foreach($result['data'] as $idx => $table_line) {
            try {
                $res = $db_conn->query("SELECT count(*) FROM " . $idx);
                if ($res !== false) {
                    foreach ($res as $row) {
                        $result['data'][$idx]['count'] = $row['count'];
                    }
                }
            } catch (PDOException $ex) {

            }
        }

        foreach($result['data'] as $idx => $table_line) {
            try {
                $res = $db_conn->query("SELECT pg_size_pretty( pg_total_relation_size('" . $idx . "') );");
                if ($res !== false) {
                    foreach ($res as $row) {
                        $result['data'][$idx]['size'] = $row['pg_size_pretty'];
                    }
                }
            } catch (PDOException $ex) {

            }
        }

        return $result;
    }

    try {
        $db_conn = new PDO("pgsql:host=" . $db_host . ';port =' . $db_port . ';dbname=' . $db_base, $db_user, $db_pass);
    } catch (PDOException $ex) {
        $db_err = $ex->getMessage();
    }

    $ip = $_SERVER['SERVER_ADDR'];
    $color = '#'.mb_substr(md5($ip), 25, 6);

    if (isset($db_conn)) {
        $tables_info = get_tables($db_conn, $db_base);
    }

?>
<style>
.indicator {
    margin-top: 8px;
    background-color: <?php echo($color) ?>;
    color: <?php echo(contrast_color($color)) ?>;
    min-height: 40px;
    padding: 8px 10px; 
}

.col_left {
    text-align: right;
}

</style>
<body>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="indicator"><?php echo($ip) ?></div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <h1>Server is online!</h1>
        </div>
    </div>
    <?php
        if ($db_err) {
    ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger" role="alert">
                DB error: <?php echo($db_err) ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <table class="table table-striped table-info table-sm">
                <tr>
                    <td>Host</td>
                    <td><?php echo $db_host; ?></td>
                </tr>
                <tr>
                    <td>Port</td>
                    <td><?php echo $db_port; ?></td>
                </tr>
                <tr>
                    <td>Base</td>
                    <td><?php echo $db_base; ?></td>
                </tr>
                <tr>
                    <td>User</td>
                    <td><?php echo $db_user; ?></td>
                </tr>
                <tr>
                    <td>Pass</td>
                    <td>********</td>
                </tr>
            </table>
        </div>
    </div>
    <?php
        } else {
    ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success" role="alert">DB connected</div>
        </div>

    <?php 
            if (isset($tables_info) && $tables_info['status'] && count($tables_info['data']) > 0) {
    ?>
        <div class="col-12">
            <table class="table table-striped table-sm">
                <tr>
                    <th>Table</th>
                    <th>Schema</th>
                    <th>Owner</th>
                    <th>Count</th>
                    <th>Size</th>
                </tr>
    <?php 
                foreach($tables_info['data'] as $table_item) {
    ?>            
                <tr>
                    <td><?php echo $table_item['table']; ?></td>
                    <td><?php echo $table_item['schema']; ?></td>
                    <td><?php echo $table_item['owner']; ?></td>
                    <td class="col_left"><?php echo $table_item['count'] ?? "Unknown"; ?></td>
                    <td class="col_left"><?php echo $table_item['size'] ?? "Unknown"; ?></td>
                </tr>
    <?php 
                }
    ?>
            </table>
        </div>
    <?php
            } else {
    ?>
                <div class="col-12">
                    <div class="alert alert-warning" role="alert">
                        The DB looks empty
                    </div>
                </div>
    <?php
            }
    ?>
        </div>
    <?php
        }
    ?>
</div>
   
    
    
</div>
</body>
</html>
