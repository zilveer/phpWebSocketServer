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

        </script>
    </head>
    <body>
        <?php
        echo "<h1>some test </h1>
        <pre>
        sock = socketWebClient();
        function kickStart() {
             if (!sock.isOpen()) {
                 /*
                  ******************************************
                  * wait until socket is open and server responded
                  * or we get an error. In both cases the above 
                  * function returns true, so we can 
                  * continue our work
                  ******************************************
                  */
                 window.setTimeout(kickStart, 100);
                 return;
             }

             xyz();
             function xyz() {
                 sock.sendMsg({'opcode': 'broadcast', 'message': 'hallo11'});
                 sock.sendMsg({'opcode': 'broadcast', 'message': 'hallo22'});
                 sock.sendMsg({'opcode': 'broadcast', 'message': 'hallo33'});
                 sock.sendMsg({'opcode': 'broadcast', 'message': 'hallo44'});
             }
         }
         window.addEventListener('load', kickStart, false);
        <pre>";
        ?>
        <script>
            /*
             ******************************************
             * we try to connect to our server and wait
             * for a responds or an error
             ******************************************
             */
            sock = socketWebClient();
            
            function kickStart() {
                if (!sock.isOpen()) {
                    /*
                     ******************************************
                     * wait until socket is open and server responded
                     * or we get an error. In both cases the above 
                     * function returns true, so we can 
                     * continue our work
                     ******************************************
                     */
                    window.setTimeout(kickStart, 100);
                    return;
                }

                xyz();
                function xyz() {
                    sock.sendMsg({'opcode': 'broadcast', 'message': 'hallo11'});
                    sock.sendMsg({'opcode': 'broadcast', 'message': 'hallo22'});
                    sock.sendMsg({'opcode': 'broadcast', 'message': 'hallo33'});
                    sock.sendMsg({'opcode': 'broadcast', 'message': 'hallo44'});
                }
            }
            window.addEventListener('load', kickStart, false);
        </script>
    </body>
</html>
