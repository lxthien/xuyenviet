<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root', array(
            'childrenAttributes' => array (
                'class' => 'nav navbar-nav',
            ),
        ));

        // About Us
        $menu->addChild('Giới thiệu', [
            'route' => 'news_show',
            'routeParameters' => ['slug' => 'gioi-thieu']
        ])
        ->setAttribute('class', 'dropdown')
        ->setLinkAttribute('class', 'dropdown-toggle')
        ->setLinkAttribute('data-toggle', 'dropdown')
        ->setChildrenAttribute('class', 'dropdown-menu');

        $menu['Giới thiệu']->addChild('Về chúng tôi', [
            'route' => 'news_show',
            'routeParameters' => ['slug' => 'gioi-thieu']
        ]);

        $menu['Giới thiệu']->addChild('Tuyển dụng', [
            'route' => 'news_show',
            'routeParameters' => ['slug' => 'tuyen-dung']
        ]);

        $menu->addChild('Dịch vụ', [
            'route' => 'news_category',
            'routeParameters' => ['level1' => 'dich-vu']
        ]);

        $menu->addChild('Xây dựng', [
            'route' => 'news_category',
            'routeParameters' => ['level1' => 'xay-dung']
        ]);

        $menu->addChild('Thiết kế', [
            'route' => 'news_category',
            'routeParameters' => ['level1' => 'thiet-ke']
        ])
        ->setAttribute('class', 'dropdown')
        ->setLinkAttribute('class', 'dropdown-toggle')
        ->setLinkAttribute('data-toggle', 'dropdown')
        ->setChildrenAttribute('class', 'dropdown-menu');

        $menu['Thiết kế']->addChild('Thiết kế quán cafe', [
            'route' => 'list_category',
            'routeParameters' => ['level1' => 'thiet-ke', 'level2' => 'thiet-ke-thi-cong-quan-cafe']
        ]);

        $menu->addChild('Sửa chữa nhà', array('uri' => 'https://suanhaminhduy.com/'))
            ->setLinkAttribute('target', '_blank')
            ->setLinkAttribute('rel', 'nofollow');

        $menu->addChild('Phong thủy xây dựng', [
            'route' => 'news_category',
            'routeParameters' => ['level1' => 'phong-thuy-xay-dung']
        ]);

        $menu->addChild('Dự án thi công', [
            'route' => 'news_category',
            'routeParameters' => ['level1' => 'du-an']
        ]);

        $menu->addChild('Tư vấn', [
            'route' => 'news_category',
            'routeParameters' => ['level1' => 'tu-van']
        ]);

        // Contact us
        $menu->addChild('Liên hệ', [
            'route' => 'contact'
        ]);

        return $menu;
    }

    public function footerMenu(FactoryInterface $factory, array $options)
    {
        $footerMenu = $factory->createItem('root');

        return $footerMenu;
    }
}