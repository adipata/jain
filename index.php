<?php
declare(strict_types = 1);

require_once 'vendor/autoload.php';

spl_autoload_register(function ($class_name) {
    include str_replace('\\', '/', $class_name) . '.php';
});
?>

<html>
 <head>
     <title>Crypto</title>
     <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro&display=swap" rel="stylesheet">
     <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
     <script src="https://unpkg.com/jquery.terminal/js/jquery.terminal.min.js"></script>
     <script src="https://requirejs.org/docs/release/2.3.5/minified/require.js"></script>

     <link rel="stylesheet" href="https://unpkg.com/jquery.terminal/css/jquery.terminal.min.css"/>
     <style>
         body{
             background-color: black;
             color: chartreuse;
             font-family: 'Source Code Pro', monospace;
         }
     </style>
     <script>
         $( document ).ready(function() {
             $('body').terminal([{
                 test: function (){
                     this.echo(testAdi());
                 },
                 del: function(){
                     let term=this;
                     return new Promise(function(resolve) {
                         setTimeout(function() {
                             term.echo("del");
                             resolve();
                         },1000);
                     });
                 }
             },"terminal.php"], {
                 login: true,
                 greetings: "Wake up, Neo...",
                 prompt: function(callback) {
                     if (this.get_token()) {
                         callback(this.login_name() + '@crypto$ ');
                     } else {
                         callback('guest@crypto$ ');
                     }
                 }
             });
         });
     </script>
 </head>
 <body></body>
</html>
