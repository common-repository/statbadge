<?php

class StatadgeHelper
{
  function GetRowStyle( $index, $prefix = 'statbadge_tr_' )
  {
    return( sprintf( ' class="%s%d"', $prefix, $index % 2 ) );
  }
  
  function GetFormfield( $name, $type, $value, $default = '', $prefix = '', $extra = array() )
  {
    if( !empty( $prefix ) )
    {
      $name = "{$prefix}[{$name}]";
    }
    
    switch( $type )
    {
      case 'radiogroup':
      {
        $data = '';
        
        foreach( $extra as $k => $v )
        {
          $data .= sprintf(
            '<input type="radio" name="%s"%s value="%s" /> %s&#160;', 
            $name, 
            $value == $k ? ' checked="checked"' : '',
            $k,
            $v
          );
        }

        return( $data );
      } 
      case 'text':
      {
        return( sprintf( '<input type="text" name="%s" value="%s"%s%s />', 
          $name, 
          empty( $value ) ? $default : $value,             
          array_key_exists( 'maxlength', $extra ) ? ' maxlength="'.$extra[ 'maxlength' ].'"' : '',
          array_key_exists( 'class', $extra ) ? ' class="'.$extra[ 'class' ].'"' : '' )
        );
      }
      case 'yesnoradio':
      {
        return(
          sprintf( '<input type="radio" name="%s"%s value="1" />%s <input type="radio" name="%s"%s value="0" />%s', 
            $name, 
            $value == 1 ? ' checked="checked"' : '',
            __( 'yes', 'statbadge' ),
            
            $name, 
            $value == 0 ? ' checked="checked"' : '',
            __( 'no', 'statbadge' )
          ) 
        );
      }
    }
  }
}

?>