<?php

namespace App\Service;



class AppService
{

    /**
     * The global per page value
     * you can create one for a custom need
     * and use it in your controller
     * @var int
     */
    const PER_PAGE = 10;

    /**
     * The twig templates for each menu item
     * This is used for admin actions
     * as it has an IsGranted('ROLE_ADMIN') annotation
     * to be rendered
     * @var array
     */
    const BACK_MENU_BOTTOM = [
        'theme/user/_menu.item.html.twig',
        'theme/notification/_menu.item.html.twig',
    ];

    /**
     * The twig templates for each menu item
     * This is used for user dashboard actions
     * as it has an IsGranted('ROLE_USER') annotation
     * to be rendered
     * @var array
     */
    public const BACK_MENU_TOP = [
        'boilerplate/_menu.item.html.twig',
    ];
}
