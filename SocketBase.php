<?php
declare(strict_types=1);
namespace yxmingy;
use Exception;

/**
 * @method listen()
 */
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
  protected $closed = false;

  /**
   * SocketBase constructor.
   * @param int $domin
   * @param int $type
   * @param null $socket
   * @throws Exception
   */
  public function __construct(int $domin = AF_INET, int $type = SOCK_STREAM, $socket = null)
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
    if($socket != null) {
      $this->socket = $socket;
    }else {
      $this->socket = socket_create($domin,$type,$this->protocol);
    }
    if($this->socket === false)
      throw $this->last_error();
  }


  /**
   * @param $resource
   * @return ServerSocket
   * @throws Exception
   */
  public function getServerInstance($resource)
  {
    return new ServerSocket($this->domin_type,$this->type,$resource);
  }

  /**
   * @param $resource
   * @param null $cid
   * @return ClientSocket
   * @throws Exception
   */
  public function getClientInstance($resource, $cid = null)
  {
    return new ClientSocket($this->domin_type,$this->type,$resource,$cid);
  }

  /**
   * @param string $address
   * @param int $port
   * @return SocketBase
   */
  public function bind(string $address = '0', int $port = 0):SocketBase
  {
    socket_bind($this->socket,$address,$port);
    return $this;
  }

  /**
   * @param SocketBase $socket
   * @return bool
   */
  public function equals(SocketBase $socket)
  {
    return $socket->getSocketResource() == $this->socket;
  }

  /**
   * @param int $length
   * @return string
   */
  public function _read(int $length)
  {
    if($this->closed) return "";
    return socket_read($this->socket,$length);
  }

  /**
   * @param int $length
   * @return string|null
   */
  public function read(int $length = 1024):?string
  {
    $data = $this->_read($length);
    if($data == "") {
      if($data === "")
        return null;
      return "";
    }
    return $data;
  }

  /**
   * @param $buffer
   * @param int $length
   * @return SocketBase
   */
  public function receive(&$buffer, int $length = 1024):SocketBase
  {
    $buffer = $this->read($length);
    return $this;
  }

  /**
   * @param string $msg
   * @param int $length
   * @return bool|int
   */
  public function _write(string $msg, int $length)
  {
    if($this->closed) return false;
    return socket_write($this->socket,$msg,$length);
  }

  /**
   * @param string $msg
   * @return SocketBase|null
   */
  public function write(string $msg):?SocketBase
  {
    $length = strlen($msg);
    while(true) {
      $sent = $this->_write($msg,$length);
      if($sent === false)
        return null;
      if($sent < $length) {
        $msg = substr($msg,$sent);
        $length -= $sent;
      }else {
        break;
      }
    }
    return $this;
  }

  /**
   * @return bool
   */
  public function shutdown():bool
  {
    return @socket_shutdown($this->socket,2);
  }

  public function close():void
  {
    $this->preClose();
    socket_close($this->socket);
  }
  
  public function safeClose():void
  {
    $this->shutdown();
    $this->close();
  }

  public function preClose():void
  {
    $this->closed = true;
  }

  /**
   * @return bool
   */
  public function closed():bool
  {
    return $this->closed;
  }

  /**
   * @return Exception
   */
  protected function last_error(): Exception
  {
    return new Exception("[Socket] Error ".socket_strerror(socket_last_error()));
  }

  /**
   * @return bool
   */
  public function setBlock():bool
  {
    return socket_set_block($this->socket);
  }

  /**
   * @return bool
   */
  public function setNonBlock():bool
  {
    return socket_set_nonblock($this->socket);
  }

  /**
   * @return resource
   */
  public function getSocketResource():resource
  {
    return $this->socket;
  }

  /**
   * @return string|null
   */
  public function getSockName():?string
  {
    $code = socket_getsockname($this->socket,$address);
    return $code ? $address : null;
  }

  /**
   * @return string|null
   */
  public function getSockAddr():?string
  {
    $code = socket_getsockname($this->socket,$address,$port);
    return $code ? $address.":".$port : null;
  }

  /**
   * @param &$name
   * @return SocketBase
   */
  public function recSockName(&$name):SocketBase
  {
    $name = $this->getSockName();
    return $this;
  }

  /**
   * @param &$addr
   * @return SocketBase
   */
  public function recSockAddr(&$addr):SocketBase
  {
    $addr = $this->getSockAddr();
    return $this;
  }
  
  
}