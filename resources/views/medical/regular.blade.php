<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title></title>
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <script>
        //rem适配（把整个屏幕的宽度当成7.5rem）
        var _html = document.getElementsByTagName('html')[0];
        var ch = document.documentElement.clientWidth;
        if (ch > 750) {
            _html.style.fontSize = '100px';
        } else {
            _html.style.fontSize = ch / 7.5 + 'px';
        }
    </script>
</head>
<body>
{!!$content!!}
</body>
</html>
