import { getComment, boundTimestamps } from "../javascript/content.js";

$(document).ready(function () {
    $(document).on("click", ".container-comments>.container-content [name='load']", getComments);
});

export function getComments() {
    const $comments = $(this).closest(".container-comments");
    const $content = $comments.find(">.container-content");
    const $parentid = $content.find("[name='parentid']");
    const $timestamp1 = $content.find("[name='timestamp1']");
    const $timestamp2 = $content.find("[name='timestamp2']");

    $.get("../php/comment-actions/get.php", { parentid: $parentid.val(), timestamp1: $timestamp1.val(), timestamp2: $timestamp2.val() })
        .done(function (response) {
            JSON.parse(response).forEach(function (info) {
                boundTimestamps($timestamp1, $timestamp2, info.creationdate);
                $content.before(getComment(info));
            });
        });
}