/* Independently authored interface behavior. */
(function(){'use strict';
function icons(){if(window.feather){window.feather.replace();}}
document.addEventListener('DOMContentLoaded',function(){
 icons();
 var lockout=document.querySelector('[data-fm-lockout-seconds]');if(lockout){var remaining=parseInt(lockout.getAttribute('data-fm-lockout-seconds'),10);if(remaining>0){var countdown=setInterval(function(){remaining--;lockout.textContent=remaining>0?'Too many login attempts. Try again in '+remaining+' seconds.':'You can try signing in again.';if(remaining<=0){clearInterval(countdown);}},1000);}}
 document.querySelectorAll('[data-fm-toggle]').forEach(function(button){button.addEventListener('click',function(){document.body.classList.toggle(window.innerWidth<992?'fm-mobile-open':'fm-collapsed');});});
 var passwordButton=document.getElementById('fm-password-toggle');if(passwordButton){passwordButton.addEventListener('click',function(){var input=document.getElementById('password');var show=input.type==='password';input.type=show?'text':'password';passwordButton.setAttribute('aria-label',show?'Hide password':'Show password');passwordButton.innerHTML='<i data-feather="'+(show?'eye-off':'eye')+'"></i>';icons();});}
 if(window.jQuery){var $=window.jQuery;var token=document.querySelector('meta[name="_token"]');if(token){$.ajaxSetup({headers:{'X-CSRF-TOKEN':token.content}});}if($.fn.tooltip){$('[data-toggle="tooltip"]').tooltip();}if($.fn.popover){$('[data-toggle="popover"]').popover();}}
});})();