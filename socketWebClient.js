function socketWebClient() {
    'uses strict';
    var
            queue = [], uuid, socket = {},
            socketOpen = false, socketSend = false;

    socket = new WebSocket('ws://localhost:8080');
    uuid = generateUUID();
    socket.onopen = function () {
        queue = [];

    };
    socket.onerror = function () {
        socketSend = false;
        socketOpen = true;
    };
    socket.onmessage = function (msg) {
        var packet;
        if (msg.data.length === 0 || msg.data.indexOf('pong') >= 0) {
            return;
        }
        packet = JSON.parse(msg.data);
        if (packet.opcode === 'ready') {
            socketOpen = true;
            socketSend = true;
            msg = {'opcode': 'uuid', 'message': uuid};
            msg = JSON.stringify(msg);
            socket.send(msg);
            return;
        }
        if (packet.opcode === 'next' && packet.uuid === uuid) {
            queue.shift();
            if (queue.length > 0) {
                msg = queue[0];
                msg = JSON.stringify(msg);
                socket.send(msg);
            }
            return;
        }
        callback(packet);
    };
    socket.onclose = function () {
        socketOpen = false;
        socketSend = false;
    };
    function callback(p) {
        /*
         ******************************************
         * dummy call back
         ******************************************
         */
        return;
    }
    function setCallback(func) {
        /*
         ******************************************
         * overwrite dummy call back with your own
         * function func
         ******************************************
         */
        callback = func;
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
        if (!socketSend) {
            return;
        }
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
            socketSend = false;
            alert('socket error: ' + ex);
        }
    }
    function quit() {
        sendMsg({'opcode': 'quit', 'role': 'thisUserRole'});
        socket.close();
        socketOpen = false;
        socketSend = false;
    }
    function isOpen() {
        return socketOpen;
    }


    return {
        'sendMsg': sendMsg,
        'uuid': function () {
            return uuid;
        }(),
        'quit': quit,
        'isOpen': isOpen,
        'setCallback': setCallback
    };
}


