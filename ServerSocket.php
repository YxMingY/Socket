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
  public function origin_accept():resource
  {
    return socket_accept($this->socket);
  }
  public function accept():ClientSocket
  {
    $socket = $this->origin_accept();
    
    return new ClientSocket(
      $this->domin_type,
      $this->type,
      $this->protocol,
      $socket
    );
  }
  public function select(array &$reads,array &$writes,array &$excepts,int $t_sec,int $t_usec = 0):int
  {
    return socket_select($reads,$writes,$excepts,$t_sec,$t_usec);
  }
}