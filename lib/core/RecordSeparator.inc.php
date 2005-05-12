<?
## $Id$

## This file is part of the CERN Document Server Software (CDSware).
## Copyright (C) 2002, 2003, 2004, 2005 CERN.
##
## The CDSware is free software; you can redistribute it and/or
## modify it under the terms of the GNU General Public License as
## published by the Free Software Foundation; either version 2 of the
## License, or (at your option) any later version.
##
## The CDSware is distributed in the hope that it will be useful, but
## WITHOUT ANY WARRANTY; without even the implied warranty of
## MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
## General Public License for more details.  
##
## You should have received a copy of the GNU General Public License
## along with CDSware; if not, write to the Free Software Foundation, Inc.,
## 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.

//==========================================================================
//  File: RecordSeparator.inc (flexElink core)
//  Classes:    XMLSimpleRecSeparator
//  Requires: 
//  Included:   
//==========================================================================

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//  Class: XMLSimpleRecSeparator 
//  Purpose:
//  Attributes:
//  Methods:
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

//This is the base class for record separation. For each different type
//  of input that requires a special record separation, this class can be
//  extended keeping the same interface:
//	constructor(file="") --> initialize the object and test input file 
//				accesibility. When file=="" standard input is
//				used
//	getRecord():array----> returns an array which contains, as first element
//		a logic indicating if everything is OK or not and as second
//		element a string containing the next record read from the input,
//		in case of success, or a error message. When eof is reached "" 
//		is returned.
//class RecSeparator {
//  function RecSeparator( $file="")
//  function getRecord()
//}

//Class for separation of XML simple inputs (one tag marks the record)
class XMLSimpleRecSeparator {
  var $fh; //Input file handler
  var $tag; //XML element tag which marks the record
  var $readnext;

  function XMLSimpleRecSeparator( $ifile="" )
  {
    $this->tag="";
  }

  function setIFile( $ifile="" )
  {
    $this->readnext="";
    //This method doesn't check if the file is correct or not, it should be 
    //  checked outside
    if(trim($ifile)=="")
    {
      $ifile="php://stdin";
    }

    $this->fh=@fopen($ifile, "r");
    if(!$this->fh)
      return "Input file handler incorrect";
    
    return "";
  }

  function setTag( $tag )
  {
    $this->tag=strtoupper(trim($tag));
  }

  function getRecord()
  {
    if($this->tag=="")
      return "";
    if(!$this->fh)
      return "";
    //Everything OK. Let's start
    $grab=0;//Flag which indicates that the start tag has been found
    $record="";//Contains the record string 
    while(!feof($this->fh))
    {
      if($this->readnext!="")
      {
	$line=$this->readnext;
	$this->readnext="";
      }
      else
	$line=fgets($this->fh, 1024);
      if($grab)
      {
	//Look if in the line there is the closing tag
	if(ereg("(.*</[ \t]*".$this->tag."[^>]*>)(.*)", strtoupper($line), $res))
	{
	  $record.=substr($line, 0, strlen($res[1]));
	  $this->readnext=substr($line, strlen($res[1]));
	  break;
	}
	else
	{
          $record.=$line;
	}
      }
      else
      { 
	//Look if in the line there is the starting tag
	if(ereg("[^<]*(<[ \t]*".$this->tag."[^>]*>.*)", strtoupper($line), $res))
	{
	  $record.=substr($line, strlen($line)-strlen($res[1]));
	  $grab=1;
	}
      }
    }
    if(feof($this->fh)&&($record==""))
    {
      fclose($this->fh);
    }

    return $record;
  }
}

?>
