{extends "protected/views/index.tpl"}
{block name="javascript"}
    <script src="{$_root}/assets/js/reportsTimesOffsList.js"></script>
    <script type="text/javascript">
    $(function() {
        $("#timeoff_autocomplete").autocomplete({
            minLength: 3,
            source: function( request, response ) {
                $.ajax({
                    url: "{$_root}/users/autocomplete",
                    dataType: "json",
                    data:{
                        name:request.term
                    },

                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label:item.name,
                                id:item.id
                            };
                        }));
                    }
                });
            },
            select: function( event, ui ) {
                $("#timeoff_autocomplete_id").val(ui.item.id);
            },
            messages: {
                noResults: '',
                results: function() {
                }
            }
        });
    });
    </script>
{/block}
    {block name="breadcrumbs"}
        <ul class="breadcrumb">
          <li><a href="{$_root}/"> Главная </a> <span class="divider">/</span></li>
          <li class="active"> Отчет по посещаемости </li>
        </ul>
    {/block}
    {block name="pagetitle"}<h1> Отчет по посещаемости </h1>{/block}
    {block name="content"}


        <form id = "reports" type='GET' action = "{$_root}/reports/timeoffs">

        <select id = 'type'>
            <option value='1'> Пользователь </option>
            <option value='2'> Отделы </option>
        </select>
        <div id="user">
            <select id='user_id' name = 'user_id'>
            {foreach from=$allUsers item=user}
                <option value = "{$user['id']}"> {$user['name']} </option>
            {/foreach}
            </select>
        </div>

        <div id="dep">
            <select id='dep_id' name = 'dep_id'>
            {foreach from=$allDep item=dep}
                <option value = "{$dep['id']}"> {$dep['name']} </option>
            {/foreach}
            </select>
        </div>


        <label for = "datepicker"> Дата </label>
        <input name = "date" type="text" id="datepicker" class='withoutDays' value = "{$timeoffsAttr['date']|date_format:"%m.%Y"}" />

    </form>
    <input form = "reports" type="submit" id="add" value = "Сформировать" class="btn btn-success" >
    <br>
    <br>
    <div class="span7">
    {if $reportAllDaysArray}
        <h3>{$name['user']}</h3>
        {include file='protected/views/Reports/timeoffs.tpl' reportAllDaysArray = $reportAllDaysArray}
    {else}
        {if $timeoffsAllUsers}
            <h3>{$name['dep']}</h3>
            {foreach from=$timeoffsAllUsers item=allUsers}
                {if $allUsers['reports']}
                    {include file='protected/views/Reports/timeoffs.tpl' reportAllDaysArray = $allUsers['reports'] tableId=$allUsers['id'] userName = $allUsers['name']}
                    {else}
                {/if}
            {/foreach}
        {else}{/if}
    {/if}
    </div>
    {/block}
{/extends}