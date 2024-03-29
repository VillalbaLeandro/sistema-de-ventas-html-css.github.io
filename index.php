<?php
include("./head.php");
?>
<title>DRINKSTORE®</title>
</head>

<body>
    <?php
    include("./header.php");
    ?>

    <main>
        <section id="bienvenida" class="seccion-bienvenida">

            <div class="bienvenidos" data-aos="fade-up">
                <h2 class="bienvenidos-title">BIENVENIDOS A </h2>
                <div class="logo-container-bienvenida">
                    <img src="./public/images/logos/logo-white.png" alt="logo-drinkstore">
                </div>
                <h2 class="bienvenidos-subtitle">Tenemos una amplia variedad de productos</h2>
                <h2 class="bienvenidos-horario">Atencion las 24hs. 7 dias a la semana</h2>
            </div>
            <div class="slider" data-aos="fade-left">
                <img src="./public/images/drink1.jpg" alt="">
            </div>

        </section>
        <div class="sobre-nosotros">
            <section id="nosotros" data-aos="fade-up">
                <h3> SOBRE NOSOTROS </h3>
                <div class=" sobre-nosotros-p">

                    <p>
                        Fundamos drinkstore en el año 2000, con una misión: ser un(a) Tienda de bebidas alcohólicas de alta
                        calidad en Posadas.
                        Nuestra pasión por la excelencia nos condujo a materializar esta misión,
                        siendo ésta la parte fundamental que nos ha impulsado a seguir adelante.
                        Nos enorgullecemos de ofrecer una experiencia de compra superior y
                        de las relaciones a largo plazo que hemos construido con nuestros clientes.
                        Visítanos hoy mismo.
                    </p>
                </div>
            </section>
            <h3 data-aos="fade-up"> NUESTROS PRODUCTOS </h3>
            <section class="products-section" id="productos" data-aos="fade-up">
                <div class="cervezas">
                    <h3>CERVEZAS</h3>
                    <article class="main">
                        <div class="card" id="c1"></div>
                        <div class="card" id="c2"></div>
                        <div class="card" id="c3"></div>
                        <div class="card" id="c4"></div>
                    </article>

                </div>
                <div class="vinos">
                    <h3>VINOS</h3>
                    <article class="main">
                        <div class="card" id="c5"></div>
                        <div class="card" id="c6"></div>
                        <div class="card" id="c7"></div>
                        <div class="card" id="c8"></div>
                    </article>
                </div>
                <div class="hielos">
                    <h3>HIELOS</h3>
                    <article class="main">
                        <div class="card" id="c9"></div>
                        <div class="card" id="c10"></div>
                        <div class="card" id="c11"></div>
                        <div class="card" id="c12"></div>
                    </article>
                </div>
            </section>
            <section class="contacto" id="contacto" data-aos="fade-up">
                <hr>
                <div class="contactanos-texto" data-aos="fade-up">
                    <h3>CONTACTÁNOS</h3>
                    <p>Av. Blas Parera 6155</p>
                    <p>drinkstore@24horas.com</p>
                    <p>+54 376 4 329985</p>
                </div>
                <div class="img-contact-contaier" data-aos="fade-up">
                    <img src="https://w.forfun.com/fetch/ab/ab4b6cb9605b3a36d87fa138ee02cabc.jpeg" alt="">
                </div>
            </section>
            <section class="ubicacion" id="ubicacion" data-aos="fade-up">
                <br>
                <hr>
                <h3>NUESTRA UBICACIÓN</h3>
                <br>
                <div class="maps-location" width="98vw">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1601.2109658400473!2d-55.93713755996546!3d-27.373778811645384!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9457bde2be6117ff%3A0x5ffecd747d7819df!2sAv.%20Blas%20Parera%206155%2C%20N3300LWN%20Posadas%2C%20Misiones!5e0!3m2!1ses!2sar!4v1684558063711!5m2!1ses!2sar" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

                </div>
            </section>
        </div>
    </main>
    <footer data-aos="fade-up">
        <h3>DRINKSTORE®</h3>
        <P>all rights reserved 2000-2023</P>
        <div class="redes-container">
            <p>seguinos en nuestras redes:</p>
            <hr>
            <div class="logos-redes" id="redes">
                <section class="buttons">
                    <a href="#" class="fa fa-facebook"></a>
                    <a href="#" class="fa fa-twitter"></a>
                    <a href="#" class="fa fa-google-plus"></a>
                    <a href="#" class="fa fa-youtube"></a>
                    <a href="#" class="fa fa-linkedin"></a>
                </section>

            </div>
        </div>
    </footer>
</body>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 1500
    });
</script>

</html>