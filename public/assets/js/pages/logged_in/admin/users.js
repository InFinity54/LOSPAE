$("#enable_selected_users").on("click", () => {
    handleAction("enable");
});

$("#disable_selected_users").on("click", () => {
    handleAction("disable");
});

function handleAction(actionName) {
    let ids = [];

    $(".userselection:checked").each(function () {
        ids.push($(this).data("user"));
    });

    window.location.href = window.location.origin + "/admin/users/" + actionName + "/" + ids.join(",");
}