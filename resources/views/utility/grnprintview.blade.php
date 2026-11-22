
<style>
			table.report1{
			font-family: "Times new Roman";
			border-collapse: collapse;
			font-size: 0.9em;
			}
			
			.report1 th,.report1 td{
			padding: 3px;
			}
			.subh2{
			font-size: 0.9em;
			white-space: nowrap;
			}
			.left{ text-align: left; }
			.right{ text-align: right; }
			
			h3,h2{
			line-height: 100%;
			}
			p {
			line-height: 0.8em;
			}
		</style>



        <div >
        @foreach ($print as $pr )

			<table  class="report1" >
				<thead>
					
					
				</thead>
				
				<tbody>
                        <tr>
                            <td>
                                {!! QrCode::size(200)->generate($pr->serial_no) !!}
                            </td>
                        </tr>
                        <tr>
                           <td>
                           {{$pr->grn_no}}<br>
                           {{$pr->serial_no}}<br>
                            {{$pr->item_name}}
                           </td> 
                        </tr>
                        
                        
				</tbody>
			</table>
            <br>
                        <br>

            @endforeach
        </div>

    


<script>
    window.print();
    window.onafterprint = function(){
        document.location.href="{!! route('barcodereprint.index') !!}";
    }
</script>
