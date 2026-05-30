<?php
set_time_limit(0);
ob_implicit_flush();

$address = '0.0.0.0'; 
$port = 8080;

$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($server, $address, $port);
socket_listen($server);

echo "🚀 Servidor de Avisos en Tiempo Real corriendo en el puerto $port...\n";

$clients = [$server];

while (true) {
    $changed = $clients;
    
    $write = NULL;
    $except = NULL;
    
    @socket_select($changed, $write, $except, NULL);
    
    if (in_array($server, $changed)) {
        $client = socket_accept($server);
        $clients[] = $client;
        
        $headers = socket_read($client, 2048);
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $match)) {
            $key = base64_encode(sha1($match[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
            $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
                       "Upgrade: websocket\r\n" .
                       "Connection: Upgrade\r\n" .
                       "Sec-WebSocket-Accept: $key\r\n\r\n";
            socket_write($client, $upgrade, strlen($upgrade));
        }
        
        $key = array_search($server, $changed);
        unset($changed[$key]);
    }
    
    foreach ($changed as $client_socket) {
        $buf = @socket_read($client_socket, 2048, PHP_NORMAL_READ);
        
        if ($buf === false || trim($buf) === 'refrescar_avisos') {
            foreach ($clients as $send_socket) {
                if ($send_socket !== $server && $send_socket !== $client_socket) {
                    $msg = 'refrescar_avisos';
                    $frame = chr(129) . chr(strlen($msg)) . $msg;
                    @socket_write($send_socket, $frame, strlen($frame));
                }
            }
            
            if ($buf === false) {
                $key = array_search($client_socket, $clients);
                @socket_close($client_socket);
                unset($clients[$key]);
            }
        }
    }
}