<?php

function envoyerEmail($destinataire, $sujet, $message)
{
    $headers = '';

    $headers .=
        "From: contact@vite-et-gourmand.fr\r\n";

    $headers .=
        "Reply-To: contact@vite-et-gourmand.fr\r\n";

    $headers .=
        "MIME-Version: 1.0\r\n";

    $headers .=
        "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail(
        $destinataire,
        $sujet,
        $message,
        $headers
    );
}