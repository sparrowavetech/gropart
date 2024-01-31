<ul class="widget-socials-share widget-socials__text">
    <li>
        <a
            class="share-facebook"
            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($product->url) }}"
            title="Facebook"
            target="_blank"
        >
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-facebook"
                        xlink:href="#svg-icon-facebook"
                    ></use>
                </svg>
            </span>
            <span class="text">Facebook</span>
        </a>
    </li>
    <li>
        <a
            class="share-twitter"
            href="https://twitter.com/intent/tweet?url={{ urlencode($product->url) }}&text={{ strip_tags(SeoHelper::getDescription()) }}"
            title="Twitter"
            target="_blank"
        >
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-twitter"
                        xlink:href="#svg-icon-twitter"
                    ></use>
                </svg>
            </span>
            <span class="text">Twitter</span>
        </a>
    </li>
    <li>
        <a
            class="share-pinterest"
            href="https://pinterest.com/pin/create/button?media={{ urlencode(RvMedia::getImageUrl($product->image, null, false, RvMedia::getDefaultImage())) }}&url={{ urlencode($product->url) }}&description={{ strip_tags(SeoHelper::getDescription()) }}"
            title="Pinterest"
            target="_blank"
        >
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-pinterest"
                        xlink:href="#svg-icon-pinterest"
                    ></use>
                </svg>
            </span>
            <span class="text">Pinterest</span>
        </a>
    </li>
    <li>
        <a
            class="share-linkedin"
            href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode($product->url) }}&summary={{ rawurldecode(strip_tags(SeoHelper::getDescription())) }}"
            title="Linkedin"
            target="_blank"
        >
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-linkedin"
                        xlink:href="#svg-icon-linkedin"
                    ></use>
                </svg>
            </span>
            <span class="text">Linkedin</span>
        </a>
    </li>
    <li>
        <a class="share-whatsapp" href="http://api.whatsapp.com/send?phone={{ theme_option('hotline') }}&text={{ urlencode('Hi, Checkout this awesome product: '.$product->url) }}&summary={{ rawurldecode(strip_tags(SeoHelper::getDescription())) }}" title="Whatsapp" target="_blank">
            <span class="svg-icon">
            <svg viewBox="5 5 22 22" class="whatsapp-ico"><path d=" M19.11 17.205c-.372 0-1.088 1.39-1.518 1.39a.63.63 0 0 1-.315-.1c-.802-.402-1.504-.817-2.163-1.447-.545-.516-1.146-1.29-1.46-1.963a.426.426 0 0 1-.073-.215c0-.33.99-.945.99-1.49 0-.143-.73-2.09-.832-2.335-.143-.372-.214-.487-.6-.487-.187 0-.36-.043-.53-.043-.302 0-.53.115-.746.315-.688.645-1.032 1.318-1.06 2.264v.114c-.015.99.472 1.977 1.017 2.78 1.23 1.82 2.506 3.41 4.554 4.34.616.287 2.035.888 2.722.888.817 0 2.15-.515 2.478-1.318.13-.33.244-.73.244-1.088 0-.058 0-.144-.03-.215-.1-.172-2.434-1.39-2.678-1.39zm-2.908 7.593c-1.747 0-3.48-.53-4.942-1.49L7.793 24.41l1.132-3.337a8.955 8.955 0 0 1-1.72-5.272c0-4.955 4.04-8.995 8.997-8.995S25.2 10.845 25.2 15.8c0 4.958-4.04 8.998-8.998 8.998zm0-19.798c-5.96 0-10.8 4.842-10.8 10.8 0 1.964.53 3.898 1.546 5.574L5 27.176l5.974-1.92a10.807 10.807 0 0 0 16.03-9.455c0-5.958-4.842-10.8-10.802-10.8z" fill-rule="evenodd"></path></svg>
            </span>
            <span class="text">Whatsapp</span>
        </a>
    </li>
</ul>
