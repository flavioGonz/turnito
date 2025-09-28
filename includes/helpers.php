<?php
function slugify($name){
  $name = preg_replace('~[^\pL\d.]+~u','-', $name);
  $name = trim($name,'-');
  $name = iconv('UTF-8','ASCII//TRANSLIT',$name);
  $name = strtolower($name);
  $name = preg_replace('~[^-a-z0-9.]+~','', $name);
  return $name ?: 'file';
}
function ext($f){return strtolower(pathinfo($f, PATHINFO_EXTENSION));}
function is_allowed($e,$cfg){return in_array($e, $cfg['ALLOWED_EXT'], true);} 
function ensure_dirs($cfg){
  foreach (['PUBLIC_ADS_PATH','STORAGE_ACTIVE','STORAGE_ARCHIVE'] as $k){
    if(!is_dir($cfg[$k])) mkdir($cfg[$k],0775,true);
  }
}
function unique_path($dir,$filename){
  $base = pathinfo($filename, PATHINFO_FILENAME);
  $e = ext($filename); $n = $base; $i=1;
  while(file_exists("$dir/$n.$e")){$i++;$n = $base.'-'.$i;}
  return "$dir/$n.$e";
}
