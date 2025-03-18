<?php
// Set current page
$current_page = 'about';
$page_title = '¿Quiénes somos?';

include 'includes/config.php';
include 'includes/db_connect.php';
include 'includes/header.php';
?>

<main class="container">
    <article class="about-page">
        <h1>¿Quiénes somos?</h1>
        <div class="about-content">
            <p>Somos un equipo dedicado a mantener informada a nuestra comunidad sobre los acontecimientos más relevantes y actuales.</p>
            
            <h2>Nuestra Misión</h2>
            <p>Proporcionar información veraz, oportuna y relevante a nuestra comunidad, promoviendo la transparencia y el acceso a la información.</p>
            
            <h2>Nuestros Valores</h2>
            <ul>
                <li>Veracidad en la información</li>
                <li>Compromiso con la comunidad</li>
                <li>Transparencia en nuestro trabajo</li>
                <li>Responsabilidad social</li>
            </ul>
            
            <h2>Contacto</h2>
            <p>Para más información o consultas, puedes contactarnos a través de:</p>
            <ul>
                <li>Email: info@proyectofenix.com</li>
                <li>Teléfono: (123) 456-7890</li>
                <li>Dirección: Calle Principal #123, Ciudad</li>
            </ul>
        </div>
    </article>
</main>

<?php include 'includes/footer.php'; ?> 