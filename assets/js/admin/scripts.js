$(function(){
   $('[data-toggle="tooltip"]').tooltip();

   $('a.js-confirm').click(function(e) {
      e.preventDefault();

      if (confirm('Are you sure?')) {
         window.location.href = this.href;
      }
   });
});
