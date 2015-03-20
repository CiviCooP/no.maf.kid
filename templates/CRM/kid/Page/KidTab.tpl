{strip}
    <br/>
    <table class="selector">        
        <tr class="columnheader">
            <th >{ts}Create date{/ts}</th>
            <th >{ts}KID Number{/ts}</th>
            <th >{ts}Earmarking{/ts}</th>
            <th >{ts}Aksjon ID{/ts}</th>
            <th >{ts}Used by entity{/ts}</th>
            <th >{ts}Used by entity ID{/ts}</th>
            <th >{ts}Created by token{/ts}</th>
        </tr>
        {foreach from=$rows item=row}
            <tr id="row_{$row.id}" class="crm-kid {cycle values="odd-row,even-row"} {$row.class}">
                <td class="crm-create_date-label">{$row.create_date}</td>
                <td class="crm-kid_number-label">{$row.kid_number}</td>
                <td class="crm-earmakring-label">{$row.earmarking}</td>
                <td class="crm-aksjon_id-label">{$row.aksjon_id}</td>
                <td class="crm-entity-label">{$row.entity}</td>
                <td class="crm-entity_id-label">{$row.entity_id}</td>
                <td class="crm-created_by_token-label">{$row.created_by_token}</td>
            </tr>
        {/foreach}
    </table>
{/strip}
