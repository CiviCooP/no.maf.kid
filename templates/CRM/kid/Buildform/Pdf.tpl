{capture name="kid" assign="kid"}
<div class="crm-accordion-wrapper">
    <div class="crm-accordion-header">KID settings</div>
    <div class="crm-accordion-body">
        <table class="form-layout-compressed">
            <tr class="crm-kid-form-block-earmarking">
                <td class="label">{$form.earmarking.label}</td>
                <td>{$form.earmarking.html}</td>
            </tr>
            <tr class="crm-kid-form-block-aksjon_id">
                <td class="label">{$form.aksjon_id.label}</td>
                <td>{$form.aksjon_id.html}</td>
            </tr>
        </table>
    </div>
</div>
{/capture}

<script type="text/javascript">
{literal}
cj(function() {
    cj('div.crm-contact-task-pdf-form-block > table.form-layout-compressed').after('{/literal}{$kid|escape:'javascript'}{literal}');
});
{/literal}
</script>