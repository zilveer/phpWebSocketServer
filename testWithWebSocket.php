<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <script src="socketWebClient.js"></script>
        <script>
            sock = socketWebClient();
        </script>
    </head>
    <body>
        <?php
        // put your code here
        ?>
        <h1>some test </h>
        <script>
            window.setTimeout(xyz, 100);
            function xyz() {
                sock.sendMsg({'opcode': 'broadcast', 'message': 'hallo'});
                sock.sendMsg({'opcode': 'broadcast', 'message': 'hallo'});
                sock.sendMsg({'opcode': 'broadcast', 'message': 'hallo'});
                sock.sendMsg({'opcode': 'broadcast', 'message': 'hallo'});
            }
        </script>
</body>
</html>
