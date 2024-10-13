$(".increaseCriteriaNumber").on("click", (event) => {
    const target = $("#" + $(event.target).data("criteria"));

    if (parseInt(target.val()) < 99) {
        target.val(parseInt(target.val()) + 1);
    }
});

$(".decreaseCriteriaNumber").on("click", (event) => {
    const target = $("#" + $(event.target).data("criteria"));

    if (parseInt(target.val()) > 0) {
        target.val(parseInt(target.val()) - 1);
    }
});