<?php
namespace yxmingy;
abstract class SocketBase
{
  const DOM_IPV4 = AF_INET;
  const DOM_IPV6 = AF_INET6;
  const DOM_LOCAL = AF_UNIX;
  const TYPE_TCP = SOCK_STREAM;
  const TYPE_UDP = SOCK_DGRAM;//DATAGRAM
  const TYPE_ICMP = SOCK_RAW;
  
  protected $socket;
  protected $domin_type;
  protected $type;
  protected $protocol;
  
  public function __construct(int $domin,int $type)
  {
    $this->domin_type = $domin;
    $this->type = $type;
    switch($type) {
      case self::TYPE_TCP:
        $this->protocol = SOL_TCP;
        break;
      case self::TYPE_UDP:
        $this->protocol = SOL_UDP;
        break;
      case self::TYPE_ICMP:
        $this->protocol = getprotobyname("icmp");
        break;
      default:
        throw new Exception("[Socket] Protocol type not exists!");
    }
    $this->socket = socket_create($domin,$type,$this->protocol);
    if($this->socket === false)
      throw $this->last_error();
  }
  
  public function __construct(int $domin,int $type,int $protocol,resource $socket)
  {
    $this->domin_type = $domin;
    $this->type = $type;
    $this->protocol = $protocol;
    $this->socket = $socket;
  }
  
  public function bind(string $address = '0',int $port = 0):SocketBase
  {
    socket_bind($this->socket,$address,$port);
    return $this->socket;
  }
  
  public function read(int $length = 1024):?string
  {
    $data = socket_read($this->socket,$length);
    if($data == "") {
      if($data === "")
        return null;
      return "";
    }
    return $data;
  }
  
  public function write(string $msg):?int
  {
    $length = strlen($msg);
    while(true) {
      $sent = $this->write($msg);
      if($sent === false)
        return null;
      if($sent < $length) {
        $msg = substr($msg,$sent);
        $length -= $sent;
      }else {
        break;
      }
    } 
  }
  
  public function shutdown():bool
  {
    return socket_shutdown($this->socket,2);
  }
  
  public function close():void
  {
    socket_close($this->socket);
  }
  
  public function safeClose():void
  {
    $this->shutdown();
    $this->close();
  }
  
  protected function last_error():Exception
  {
    return new Exception("[Socket] Error ".socket_strerror(socket_last_error()));
  }
  
  public function setBlock():bool
  {
    return socket_set_block($this->socket);
  }
  
  public function setNonBlock():bool
  {
    return socket_set_nonblock($this->socket);
  }
  
  public function getSocketResource():resource
  {
    return $this->socket;
  }
  
}