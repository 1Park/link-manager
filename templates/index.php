<!DOCTYPE html>
<html>

<head>
    <title>Shared file page</title>
    <style>
        /* Necessary style definitions for the page */
        /*
        .shared-items-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .no-items {
            text-align: center;
            color: #666;
            font-size: 1.2em;
            padding: 20px;
        }

        .shared-items-list {
            list-style: none;
            padding: 0;
        }

        .shared-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            transition: background 0.2s;
        }

        .shared-item:hover {
            background: #f0f0f0;
        }

        .item-info {
            font-size: 1.1em;
            color: #333;
        }

        .item-url {
            margin-left: 10px;
        }

        .external-link {
            text-decoration: none;
            color: #007bff;
            padding: 5px 10px;
            border: 1px solid #007bff;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .external-link:hover {
            background: #007bff;
            color: white;
        }

        */

        myPageContent {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        myHeader {
            background-color: #4CAFF0;
            color: #fff;
            border-radius: 10px;
            padding: 10px;
            font-size: 36px;
            margin-bottom: 20px;
        }

        /* 공유 목록 스타일 */
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin: 10px 0;
            font-size: 18px;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <myPageContent>
        <!-- 공유 파일 목록 -->
        <myHeader>Shared Files</myHeader>
        <div class="shared-items-container">
    <?php if (empty($sharedItems)) { ?>
        <div class="no-items">No shared files found.</div>
            <?php } else { ?>
                <ul class="shared-items-list">
                    <?php foreach ($sharedItems as $item) { ?>
                        <li class="shared-item">
                            <span class="item-info">
                                <?php echo htmlspecialchars(ucfirst($item['type']) . ': ' . basename($item['path'])); ?>
                            </span>
                            <?php if (!empty($item['externalURL'])) { ?>
                                <span class="item-url">
                                    <a href="<?php echo htmlspecialchars($item['externalURL']); ?>" target="_blank" class="external-link">Download Link</a>
                                </span>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
    </myPageContent>
</body>

</html>