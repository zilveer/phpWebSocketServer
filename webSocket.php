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
    </head>
    <body>
        <?php
        // put your code here
        ?>
        <h1>some test </h>
        <script>
            /* global thisUser, sideNav, navigateListActions, pubcursor, 
             * tpp, pubid, thisUserRole, thisUserName, thisUseSSL, 
             * dialogs, workActions, feedback, thisIP */
            var uuid, warned = false, sockTimer = null, host, protocol = 'ws://';

            try {
                socket = new WebSocket('ws://localhost:8080');

                socket.onopen = function (msg) {
                    if (warned) {

                        warned = false;
                        sockTimer = null;
                    }
                    b = bus();

//                    b.sendMsg({opcode: 'broadcast', message: 'm1'});
//                    b.sendMsg({opcode: 'broadcast', message: 'm2'});
//                    b.sendMsg({opcode: 'broadcast', message: 'm3'});
//                    b.sendMsg({opcode: 'broadcast', message: 'm4'});
                    b.sendMsg({'opcode': '1'});
                    b.sendMsg({'opcode': '2'});
                    b.sendMsg({'opcode': '3'});
                    b.sendMsg({'opcode': '4'});
                    b.quit();
                };
                socket.onerror = function (msg) {
                    if (!warned) {

                        warned = true;
                        //   sockTimer = setInterval(init, 1000 * 60);
                    }
                };
                socket.onmessage = function (msg) {
                    var i, packet;
                    if (msg.data.length === 0 || msg.data.indexOf('pong') >= 0) {
                        return;
                    }
                    packet = JSON.parse(msg.data);
                    alert('packet');
                };
                socket.onclose = function (msg) {
                    alert('close');
                };
            } catch (ex) {
                // log(ex);
            }
            function bus() {
                'uses strict';
                var uuid, warned = false, sockTimer = null, host, protocol = 'ws://';


                function init() {
                    var obj;

                }
                function generateUUID() { // Public Domain/MIT
                    var d = new Date().getTime();
                    if (typeof performance !== 'undefined' && typeof performance.now === 'function') {
                        d += performance.now(); //use high-precision timer if available
                    }
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                        var r = (d + Math.random() * 16) % 16 | 0;
                        d = Math.floor(d / 16);
                        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
                    });
                }



                function sendMsg(msg) {
                    var l, msg;
                    try {
                        msg = JSON.stringify(msg);
                        l = msg.length + '';
                      //  socket.send(JSON.stringify({len: l}));
                        //  socket.send(l);
                        socket.send(msg);
                        //log('Sent: ' + msg);
                    } catch (ex) {
                        alert('socket error: ' + ex);
                    }
                }
                function quit() {
                    sendMsg({'opcode': 'quit', 'role': 'thisUserRole'});
                    socket.close();
                    busOpen = false;
                }


                uuid = generateUUID();
                init();
                return {
                    'sendMsg': sendMsg,
                    'uuid': function () {
                        return uuid;
                    }(),
                    'quit': quit
                };
            }



        </script>
</body>
</html>
