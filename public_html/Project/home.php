<?php
require(__DIR__ . "/../../partials/nav.php");
if (is_logged_in(true)) {
    $username = get_username();

    // Get recently added trails
    $trails = get_latest_trails();

    function get_diff_html($difficulty){
        switch ($difficulty) {
            case "Easiest":
                return '<div>Difficulty <span class="rating">★☆☆☆</span></div>';
                break;
            case "Beginner":
                return '<div>Difficulty <span class="rating">★★☆☆</span></div>';
                break;
            case "Intermediate":
                return '<div>Difficulty <span class="rating">★★★☆</span></div>';
                break;
            case "Advanced":
                return '<div>Difficulty <span class="rating">★★★★</span></div>';
                break;
            default:
                return '<div>Difficulty <span class="rating">☆☆☆☆</span></div>';
                break;
        }
    }
}

function get_image_url($thumb){
    if (empty($thumb) || $thumb == null){
        echo './images/placeholder.jpg';
    } else {
        echo $thumb;
    }
}

?>

<body class="bg-dark">
    <div class="col-lg-12">
        <div class="container mt-5 mb-4 p-5 rounded-2" style="background-color: #ffffff;">
            <h1>Welcome <?php echo $username ?>!</h1>
            <h6>Click a trail to view details</h6>
        </div>
        <div class="container mt-5 mb-4 p-5 rounded-2" style="background-color: #ffffff;">
            <div class="row ms-2">
                <div class="reviews-top col-lg-12">
                    <h1 class="mb-2"><b>Recently added Trails</b></h1>
                </div>
            </div>
            <?php if (count($trails) == 6) : ?>
                <div id="carousel-items" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-nav row">
                        <button class="page-link" type="button" data-bs-target="#carousel-items" data-bs-slide="prev">
                            <span>←</span>
                        </button>
                        <button class="page-link ms-2" type="button" data-bs-target="#carousel-items" data-bs-slide="next">
                            <span >→</span>
                        </button>
                    </div>
                    <div class="carousel-inner pt-5">
                        <div class="carousel-item active">
                            <div class="row">
                                <?php for ($i = 0; $i < 3; $i++) : ?>
                                    <div class="col-md-4">
                                        <a href="./trail.php?id=<?php se($trails[$i], "id", "", true) ?>" style="text-decoration: none;">
                                            <div class="card">
                                            <div class="card-body" style="background-size: cover; background-image: url( <?php get_image_url(se($trails[$i], "thumbnail", "", false)); ?>)">
                                            </div>
                                                <div class="card-footer">
                                                    <div><strong><?php se($trails[$i], "name", "", true); ?></strong></div>
                                                    <?php echo get_diff_html($trails[$i]["difficulty"]) ?>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="row">
                                <?php for ($i = 3; $i < 6; $i++) : ?>
                                    <div class="col-md-4">
                                        <a href="./trail.php?id=<?php se($trails[$i], "id", "", true) ?>" style="text-decoration: none;">
                                            <div class="card">
                                                <div class="card-body" style="background-size: cover; background-image: url( <?php get_image_url(se($trails[$i], "thumbnail", "", false)); ?>)">
                                                </div>
                                                <div class="card-footer">
                                                    <div><strong><?php se($trails[$i], "name", "", true); ?></strong></div>
                                                    <?php echo get_diff_html($trails[$i]["difficulty"]) ?>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>