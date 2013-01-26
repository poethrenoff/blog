<style>
	.error {
		font-size: 12px;
		font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
		
		color: red;
		font-weight: bold;
	}
	.message {
		text-align: center;
	}
	.extra {
		font-size: 10px;
		text-align: left;
		
		margin-top: 20px;
	}
</style>
<div class="error message">
	{$message|escape}
</div>
{if $file}
<div class="error extra">
	{$file|escape}({$line|escape})<br/><br/>
	{$trace|escape|nl2br}
</div>
{/if}
