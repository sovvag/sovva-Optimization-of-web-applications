<?php
// Карта сайта для поисковиков: /sitemap.xml (строится из базы данных).
// В неё попадают только публичные страницы. Кабинеты и формы входа не нужны.
class SitemapController extends Controller
{
    public function index()
    {
        $urls = [
            ['loc' => absolute_url('/'),        'freq' => 'weekly',  'priority' => '1.0'],
            ['loc' => absolute_url('/doctors'), 'freq' => 'weekly',  'priority' => '0.9'],
            ['loc' => absolute_url('/prices'),  'freq' => 'weekly',  'priority' => '0.8'],
            ['loc' => absolute_url('/contacts'),'freq' => 'weekly',  'priority' => '0.8'],
        ];
        foreach ((new Doctor())->allActive() as $d) {
            $urls[] = ['loc' => absolute_url('/doctors/' . $d['slug']), 'freq' => 'monthly', 'priority' => '0.8'];
        }

        $articles = require BASE_PATH . '/config/articles.php';
        foreach ($articles as $slug => $a) {
            $urls[] = ['loc' => absolute_url('/articles/' . $slug), 'freq' => 'monthly', 'priority' => '0.7'];
        }
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            echo "  <url>\n";
            echo '    <loc>' . e($u['loc']) . "</loc>\n";
            echo '    <changefreq>' . $u['freq'] . "</changefreq>\n";
            echo '    <priority>' . $u['priority'] . "</priority>\n";
            echo "  </url>\n";
        }
        echo '</urlset>';
    }
}
