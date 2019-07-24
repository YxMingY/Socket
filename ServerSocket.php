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
    $creads = SocketBase::getSocketResources($reads);
    $cwrites = SocketBase::getSocketResources($writes);
    $cexcepts = SocketBase::getSocketResources($excepts);
    $reads = $writes = $excepts = [];
    $code = $this->select($creads,$cwrites,$cexcepts,$t_sec,$t_usec);
    if($code > 0) {
      if(in_array($this->socket,$creads)) {
        $reads[] = $this;
        $key = array_search($this->socket,$creads);
        unset($creads[$key]);
      }
      foreach($creads as $read) {
          $reads[] = $this->getClientInstance($read);
      }
      foreach($cwrites as $write) {
          $writes[] = $this->getClientInstance($write);
      }
      foreach($cexcepts as $except) {
          $excepts[] = $this->getClientInstance($except);
      }
    }
    return $code;
  }
  public function selectNewClient():?ClientSocket
  {
    $reads = [$this,];
    $writes = $excepts = [];
    $code = $this->select($reads,$writes,$excepts,0);
    if($code > 0 && in_array($this,$reads)) {
      return $this->getClientInstance($this->accept());
    }
    return null;
  }
}