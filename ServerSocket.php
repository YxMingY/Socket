<?php
namespace yxmingy;
class ServerSocket extends SocketBase
{
  
  const SELECT_BLOCK = null;
  const SELECT_NONBLOCK = 0;
  
  public function listen(int $backlog = 0):ServerSocket
  {
    if(socket_listen($this->socket,$backlog) === false)
      throw $this->last_error();
    return $this;
  }
  public function _accept()
  {
    return socket_accept($this->socket);
  }
  public function accept():ClientSocket
  {
    $socket = $this->_accept();
    return $this->getClientInstance($socket);
  }
  public function _select(array &$reads,array &$writes,array &$excepts,int $t_sec,int $t_usec = 0):int
  {
    return socket_select($reads,$writes,$excepts,$t_sec,$t_usec);
  }
  
  public function select(array &$reads,array &$writes,array &$excepts,int $t_sec,int $t_usec = 0):int
  {
    $reads = SocketBase::getSocketResources($reads);
    $writes = SocketBase::getSocketResources($writes);
    $excepts = SocketBase::getSocketResources($excepts);
    $code = $this->select($reads,$writes,$excepts,$t_sec,$t_usec);
    if($code > 0) {
      foreach($writes)
    }
  }
  
}