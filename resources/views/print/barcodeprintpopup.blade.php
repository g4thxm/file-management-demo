<div id="printable-data" style="display: none;">
{{ $printable_content1 }}

</div>

<script>
    var left = (screen.width/2)-(660/2);
    var top = 130;//(screen.height/2)-(h/2);

    if( window.printWindow && !window.printWindow.closed){
        window.printWindow.focus();
        window.printWindow.document.body.innerHTML='';
        window.printWindow.moveTo(left,top);
    }
    else{
        // try{window.printWindow.close();}catch(E){}
        window.printWindow = window.open("", "Print", "toolbar=no,scrollbars=yes,resizable=yes,width=660,height=400,top="+top+",left="+left+"");
        var css = document.createElement("link");
        css.type = "text/css";
        css.rel = "stylesheet";
        css.href = "{{asset('css/barcodeprint.css')}}";

        var title = document.createElement("title");
        title.innerHTML = 'Print';

        window.printWindow.document.head.appendChild(css);
        window.printWindow.document.head.appendChild(title);

    }

    var content = document.createElement('BODY');
    content.id = 'printing';
    content.innerHTML = '';

    var pre = document.createElement('pre');
    pre.innerHTML = document.getElementById('printable-data').innerHTML;
    content.append( pre );

    window.printWindow.document.body=content;
    window.printWindow.focus();
    window.printWindow.print();

    var exit_time = content.innerHTML.length.toString().length;
    exit_time = Math.pow(2,10+exit_time); // based on data to print close window after some time
    setTimeout(function () {
        window.printWindow.close();
    },exit_time);
</script>
</html>
