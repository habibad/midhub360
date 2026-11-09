    
   /* To Disable right button (Inspect) Element start*/
   $(document).bind("contextmenu",function(e) {
    e.preventDefault();
   });
   
   $(document).keydown(function(e){
       if(e.which === 123){
          return false;
       }
   });
   
   /* To Disable right button (Inspect) Element end */
    
   /* To Disable keyboard key Element Start */      
 
   document.onkeydown = function(e) {
   if(event.keyCode == 123) {
   return false;
   }
   if(e.ctrlKey && e.keyCode == 'E'.charCodeAt(0)){
   return false;
   }
   if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)){
   return false;
   }
   if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)){
   return false;
   }
   if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)){
   return false;
   }
   if(e.ctrlKey && e.keyCode == 'S'.charCodeAt(0)){
   return false;
   }
   if(e.ctrlKey && e.keyCode == 'H'.charCodeAt(0)){
   return false;
   }
   if(e.ctrlKey && e.keyCode == 'A'.charCodeAt(0)){
   return false;
   }
   if(e.ctrlKey && e.keyCode == 'F'.charCodeAt(0)){
   return false;
   }
   if(e.ctrlKey && e.keyCode == 'E'.charCodeAt(0)){
   return false;
   }
   }
   
   /* To Disable keyboard key Element end */
      
      