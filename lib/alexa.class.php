<?php

class Alexa
{
  function Query( $url )
  {
    $skip = array( 'alexa.com', 'www.alexa.com' );
    
    global $blacklist;
    
    $blacklist = array(); # file( 'blacklist.txt' );
    
    $blacklist = array_unique( array_map( 'trim', $blacklist ) );
    
    include_once( 'lib/linkfetcher.class.php' );
    
    $LinkFetcher = new LinkFetcher(
      array(
        'timeout' => 25, 
        'attributes' => array( 'href' )
      )
    );
  
    $keywords = array();
    $result = array();
    $index = 0; 
    $ourl = $url;
    
    $host = GetHost( $url );
    if( eregi( "^www\.", $host ) )
    {
      $host = substr( $host, 4 );
    }
    
    $url = sprintf( 'http://www.alexa.com/data/ds/linksin/%s?q=link:%s', $host, $host );

    do
    {
      $LinkFetcher->LoadUrl( $url . ($index>0?'&page='.($index+1) : '' ));

      foreach( $LinkFetcher->GetExternalLinks() as $link )
      {
        if( empty( $link->text ) || strcmp( $link->text, $link->href ) == 0 )
        {
          continue;
        }

        $host = GetHost( $link->href );
        
        if( $host === false || ereg_in_array( $host, $skip ) )
        {
          continue;
        }
        
        $result[] = array( 'alexa', $host, $link->text, $link->href, 1 );
      }

      file_put_contents( getcwd() . '/logs/alexa_' . GetHost( $ourl ) . '_' . $index . '.html', '<xmp>'.$url.$index.'</xmp>'. $LinkFetcher->buffer ); 
      
      $index ++;

    }
    while( eregi( "linksin=([0-9].*)&amp;page=" . ( $index+1), $LinkFetcher->buffer ) !== false );
      
    return( $result );
  }
}

?>