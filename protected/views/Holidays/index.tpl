{extends "protected/views/index.tpl"}
{block name="pagetitle"}<h1>Выходые дни</h1>{/block}

{block name="content"}
    <script type="text/javascript">
    $(document).ready(function () {
        $("#auto").click(function(){
            $("select#holiday").val(1);
            $("select#holiday").attr("class","text-error");
        });
    });
</script>
    <div class="span7">
        <table class="table table-bordered">
            <thead>
                <th> День недели </th>
                <th> Дата </th>
                <th> Тип </th>
            </thead>
            <tbody>
            <form method="POST" id="type">
                {foreach from=$holidays item=holiday}
                    <tr>
                        <td>{$holiday['days']}</td>
                        <td> {$holiday['date']}</td>
                        <td>
                            <select name="{$holiday['date']}"{if $holiday['trigger']==1} id="holiday"{/if} {if $holiday['type']==1 or $holiday['type']==2} class="text-error"{/if}>
                                <option value="0"{if $holiday['trigger']==0} selected {/if}>Рабочий</option>
                                {html_options values=$values output=$types selected=$holiday['type']}
                            </select>
                       </td>
                    </tr>
                {/foreach}
            </tbody>
            </form>
        </table>
        <button form="type" class="btn btn-success" type="submit">Сохранить</button>
        <button id="auto">Автозаполнение</button>
    </div>
{/block}
{/extends}