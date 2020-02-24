<?php
namespace yxmingy;
class ClientSocket extends SocketBase
{
  protected $cid = null;

  //Used when selected
  public function __construct(int $domin = AF_INET,int $type = SOCK_STREAM, $socket = null, $cid = null)
  {
    parent::__construct($domin,$type,$socket);
    $this->cid = $cid;
  }

  /**
   * @param string $address
   * @param int $port
   * @return ClientSocket
   * @throws \Exception
   */
  public function connect(string $address, int $port = 0):ClientSocket
  {
    if(socket_connect($this->socket,$address,$port) === false)
      throw $this->last_error();
    return $this;
  }

  /**
   * @return string|null
   */
  public function getPeerName():?string
  {
    $code = socket_getpeername($this->socket,$address);
    return $code ? $address : null;
  }

  /**
   * @return string|null
   */
  public function getPeerAddr():?string
  {
    $code = socket_getpeername($this->socket,$address,$port);
    return $code ? $address.":".$port : null;
  }

  /**
   * @return string|null
   */
  public function cid():?string
  {
    if($this->cid === null){
      $this->cid = md5($this->getPeerAddr());
    }
    return $this->cid;
  }

  /**
   * @param $name
   * @return SocketBase
   */
  public function recPeerName(&$name):SocketBase
  {
    $name = $this->getPeerName();
    return $this;
  }

  /**
   * @param $addr
   * @return SocketBase
   */
  public function recPeerAddr(&$addr):SocketBase
  {
    $addr = $this->getPeerAddr();
    return $this;
  }
}