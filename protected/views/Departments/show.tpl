{extends "protected/views/index.tpl"}

{block name="content"}
<div class="span7">
    <table class="table table-bordered">
        <th> Имя </th>
        <th> Должность </th>

        {foreach from=$users item=user}
            <tr>
                <td> {$user['name']} </td>
                <td> -todo- </td>
            </tr>
        {/foreach}
   </table>
</div>
{/block}

{/extends}