<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Swagger UI</title>
        <link rel="stylesheet" type="text/css" href="../swagger/swagger-ui.css">
        <style>
            html {
                box-sizing: border-box;
                overflow: -moz-scrollbars-vertical;
                overflow-y: scroll;
            }

            *,
            *:before,
            *:after {
                box-sizing: inherit;
            }

            body {
                margin: 0;
                background: #fafafa;
            }

            .opblock_summary {
                border-color: violet;
            }

            .opblock.opblock-list {
                border-color: violet;
                background: rgba(97,175,254,.1);
            }

            .knop {
                background: #618a12;
            }

        </style>
    </head>

    <body>
        <div id="swagger-ui">
            <div class="swagger-ui">
                <div class="wrapper">
                    <h4 class="opblock-tag">
                            <span>Helios</span><small>
                            <div class="markdown">
                                <p>API beschrijving voor de beschikbare web services</p>
                            </div>
                        </small>
                    </h4>

                   
                    <?php

                        $html = "
                            <div style='height: auto; border: none; margin: 0px; padding: 0px;'>
                                <div class='opblock opblock-list'>
                                    <div class='opblock-summary opblock-summary-get'>
                                        <a class='nostyle' style='margin-right: 10px; font-weight:600;' href='../swagger/index.html?url=/docs/#yml#'>
                                            <span>​#title#</span>
                                        </a>
                                        <div class='opblock-summary-description'>#descr#</div>
                                    </div>
                                </div>
                            </div>";

                        $files = glob("./*.yml");
                        foreach($files as $file) {
                            $parsed = yaml_parse_file ($file);
                            $object = substr($file, 2, strlen($file)-6);

                            $output = str_replace ("#title#", $object,$html);
                            $output = str_replace ("#yml#", $file ,$output);
                            $output = str_replace ("#descr#", $parsed["info"]["description"] ,$output);

                            echo $output;
                        }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>