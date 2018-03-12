
function socketWebClient() {
    'uses strict';
    var queue = [], uuid, socketOpen = false, sockTimer = null, host, protocol = 'ws://';

    function init() {
        try {

            socket = new WebSocket('ws://localhost:8080');
            socket.onopen = function (msg) {
                if (queue.length > 0) {
                     queue = []
                }
            };
            socket.onerror = function (msg) {

            };
            socket.onmessage = function (msg) {
                var packet;
                if (msg.data.length === 0 || msg.data.indexOf('pong') >= 0) {
                    return;
                }
                packet = JSON.parse(msg.data);
                if (packet.opcode === 'ready') {
                    socketOpen = true;
                    msg = {'opcode': 'uuid', 'message': uuid};
                    msg = JSON.stringify(msg);
                    socket.send(msg);
                }
                if (packet.opcode === 'next') {
                    queue.shift();
                    if (queue.length > 0) {
                        msg = queue[0];
                        msg = JSON.stringify(msg);
                        socket.send(msg);
                    }
                }
            };
            socket.onclose = function (msg) {
                alert('close');
            };
        } catch (ex) {
            // log(ex);
        }

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
        var msg;
        try {
            if (socketOpen) {
                queue.push(msg);
            }
            if (queue.length === 1 && socketOpen) {
                msg = queue[0];
                msg = JSON.stringify(msg);
                socket.send(msg);
            }
        } catch (ex) {
            alert('socket error: ' + ex);
        }
    }
    function quit() {
        sendMsg({'opcode': 'quit', 'role': 'thisUserRole'});
        socket.close();
        busOpen = false;
    }
    function isOpen() {
        return true;//SsocketOpen;
    }

    uuid = generateUUID();
    init();
    return {
        'sendMsg': sendMsg,
        'uuid': function () {
            return uuid;
        }(),
        'quit': quit,
        'isOpen': isOpen
    };
}


