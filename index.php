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
         var trm;

         function select_file(){
             document.getElementById('input_file').click();
         }

         function upload_file(){
             $.ajax({
                 url: 'upload.php',
                 type: 'POST',
                 data: new FormData($('#fileForm')[0]), // The form with the file inputs.
                 processData: false,
                 contentType: false                    // Using FormData, no need to process data.
             }).done(function(data){
                 trm.echo("Buffer: "+data);
             }).fail(function(){
                 trm.echo("An error occurred, buffer could not be created!");
             });
         }

         $( document ).ready(function() {
             $('body').terminal([{
                 sf: function(){
                     select_file();
                 },
                 bu:function (){
                     trm=this;
                     $("#token_form").val(this.get_token());
                     upload_file();
                 },
                 test: function (){
                     this.echo("Test");
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
 <body>
    <form style="display: none" id="fileForm">
        <input type="file" name="input_file" id="input_file" hidden>
        <input type="text" name="token_form" id="token_form" hidden>
    </form>
 </body>
</html>
