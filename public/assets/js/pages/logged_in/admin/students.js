$("#studentglobalselection").change((event) => {
    if($("#studentglobalselection").is(":checked")) {
        $(".studentselection").prop("checked", true);
    } else {
        $(".studentselection").prop("checked", false);
    }
})

$("#reset_selected_students").on("click", () => {
    handleAction("reset");
});

$("#edit_selected_students").on("click", () => {
    handleAction("edit");
});

$("#enable_selected_students").on("click", () => {
    handleAction("enable");
});

$("#disable_selected_students").on("click", () => {
    handleAction("disable");
});

$("#delete_selected_students").on("click", () => {
    handleAction("remove");
});

function handleAction(actionName) {
    let ids = [];

    $(".studentselection:checked").each(function () {
        ids.push($(this).data("student"));
    });

    window.location.href = window.location.origin + "/admin/students/" + actionName + "/" + ids.join(",");
}