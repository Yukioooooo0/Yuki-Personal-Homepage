<?php
// --- 配置区 ---

// 您要解析的API地址 (请确保末尾有斜杠)
$api_base_url = 'https://cj.lziapi.com/api.php/provide/vod/from/lzm3u8/at/xml/';

// --- 逻辑区 (请勿轻易修改) ---

// 1. 获取用户输入参数
$page = 1;
if (isset($_GET['pg']) && is_numeric($_GET['pg'])) {
    $page = intval($_GET['pg']); // 获取页码
}

$search_word = '';
if (isset($_GET['wd']) && !empty($_GET['wd'])) {
    $search_word = trim($_GET['wd']); // 获取搜索词
}

// 2. 构造最终的API请求URL
$query_params = [];
if ($page > 1) {
    $query_params['pg'] = $page;
}
if (!empty($search_word)) {
    $query_params['wd'] = $search_word; // 搜索词
}

$fetch_url = $api_base_url;
if (!empty($query_params)) {
    $fetch_url .= '?' . http_build_query($query_params);
}

// 3. 使用cURL抓取数据 (更稳定)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fetch_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 15); // 15秒超时
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
// (注意) 对于初学者，如果遇到SSL证书问题，可以取消下面一行的注释，但这不安全，仅供测试
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$xml_string = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

// 4. 解析XML数据
$xml_data = null;
$error_message = '';

if ($curl_error) {
    $error_message = 'cURL 抓取错误: ' . htmlspecialchars($curl_error);
} elseif (empty($xml_string)) {
    $error_message = 'API返回了空数据，可能是接口已失效或服务器IP被禁止。';
} else {
    // 禁用XML内部错误，防止其直接输出到页面
    libxml_use_internal_errors(true);
    $xml_data = simplexml_load_string($xml_string);
    
    if ($xml_data === false) {
        $error_message = '无法解析XML数据。请检查API地址是否正确，或返回的内容是否为标准XML。';
    }
}

// 5. 准备分页和列表数据 (如果XML解析成功)
$video_list = [];
$pagination = [
    'page' => 1,
    'pagecount' => 1,
    'pagesize' => 20,
    'recordcount' => 0
];

if ($xml_data) {
    // 获取 <list> ... </list> 中的所有 <video>
    $video_list = $xml_data->list->video;
    
    // 获取 <rss> 标签上的分页属性
    $attrs = $xml_data->attributes();
    $pagination = [
        'page' => (int)$attrs['page'],
        'pagecount' => (int)$attrs['pagecount'],
        'pagesize' => (int)$attrs['pagesize'],
        'recordcount' => (int)$attrs['recordcount']
    ];
}

/**
 * 辅助函数：构建分页链接
 * @param array $base_params - 基础参数 (如搜索词)
 * @param int $target_page - 目标页码
 * @return string
 */
function build_link($base_params, $target_page) {
    $params = $base_params;
    if ($target_page > 1) {
        $params['pg'] = $target_page;
    }
    return '?' . http_build_query($params);
}

// 准备分页链接的基础参数 (保留搜索词)
$pagination_base_params = [];
if (!empty($search_word)) {
    $pagination_base_params['wd'] = $search_word;
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API 采集解析器</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f7f6;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .header {
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
        }
        .header h1 {
            margin: 0;
            color: #1a1a1a;
        }
        .search-form {
            margin-top: 15px;
        }
        .search-form input[type="text"] {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            font-size: 16px;
        }
        .search-form button {
            padding: 10px 20px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .content {
            padding: 30px;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px 20px;
            border-radius: 5px;
            word-break: break-all;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        .data-table th {
            background-color: #f9f9f9;
            font-weight: 600;
        }
        .data-table tr:hover {
            background-color: #f5f5f5;
        }
        .pagination {
            padding-top: 20px;
            text-align: center;
        }
        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 4px;
            border-radius: 4px;
            text-decoration: none;
            color: #007bff;
            border: 1px solid #ddd;
        }
        .pagination span.current {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .pagination a:hover {
            background-color: #f0f0f0;
        }
        .pagination span.disabled {
            color: #aaa;
            border-color: #eee;
        }
        .footer-info {
            text-align: center;
            padding: 10px 0;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>LZI API 解析器 (<?php echo htmlspecialchars($api_base_url); ?>)</h1>
            
            <form class="search-form" method="GET" action="">
                <input type="text" name="wd" placeholder="输入关键词搜索..." value="<?php echo htmlspecialchars($search_word); ?>">
                <button type="submit">搜索</button>
            </form>
        </div>

        <div class="content">
            <?php if ($error_message): ?>
                <div class="error">
                    <strong>糟糕，出错了：</strong>
                    <p><?php echo $error_message; ?></p>
                </div>

            <?php elseif ($xml_data): ?>
                <div class="footer-info">
                    共找到 <?php echo $pagination['recordcount']; ?> 条数据，
                    当前第 <?php echo $pagination['page']; ?> / <?php echo $pagination['pagecount']; ?> 页
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>名称</th>
                            <th>分类</th>
                            <th>播放来源</th>
                            <th>最后更新时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($video_list as $video): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($video->id); ?></td>
                                <td><?php echo htmlspecialchars($video->name); ?></td>
                                <td><?php echo htmlspecialchars($video->type); ?></td>
                                <td><?php echo htmlspecialchars($video->dt); ?></td>
                                <td><?php echo htmlspecialchars($video->last); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($video_list)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 30px;">
                                    没有找到数据。
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <?php 
                        $current_page = $pagination['page'];
                        $total_pages = $pagination['pagecount'];
                    ?>

                    <?php if ($current_page > 1): ?>
                        <a href="<?php echo build_link($pagination_base_params, 1); ?>">首页</a>
                    <?php else: ?>
                        <span class="disabled">首页</span>
                    <?php endif; ?>

                    <?php if ($current_page > 1): ?>
                        <a href="<?php echo build_link($pagination_base_params, $current_page - 1); ?>">上一页</a>
                    <?php else: ?>
                        <span class="disabled">上一页</span>
                    <?php endif; ?>

                    <span class="current"><?php echo $current_page; ?></span>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo build_link($pagination_base_params, $current_page + 1); ?>">下一页</a>
                    <?php else: ?>
                        <span class="disabled">下一页</span>
                    <?php endif; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo build_link($pagination_base_params, $total_pages); ?>">尾页 (<?php echo $total_pages; ?>)</a>
                    <?php else: ?>
                        <span class="disabled">尾页</span>
                    <?php endif; ?>

                </div>

            <?php endif; ?>
        </div>
    </div>

</body>
</html>