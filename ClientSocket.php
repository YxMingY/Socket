<?php
namespace yxmingy;
class ClientSocket extends SocketBase
{
  public function connect(string $address,int $port = 0):ClientSocket
  {
    if(socket_connect($this->socket,$address,$port) === false)
      throw $this->last_error();
    return $this;
  }
}