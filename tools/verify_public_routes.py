"""Local HTTP/SEO regression checks; restore the staging indexing flag in all cases."""
from concurrent.futures import ThreadPoolExecutor
import json
import re
import subprocess
from urllib.request import urlopen


def php(code):
    result = subprocess.run(
        ['docker', 'compose', 'exec', '-T', 'wordpress', 'php'],
        input="<?php require '/var/www/html/wp-load.php'; "
              "if(!str_contains(home_url(),'localhost')){exit(1);}" + code,
        text=True, encoding='utf-8', capture_output=True, check=True,
    )
    return result.stdout


original = json.loads(php('echo wp_json_encode(get_option("blog_public"));'))
shared = json.loads(php('$out=[];foreach(["header","footer"] as $type){$p=get_posts(["post_type"=>"elementor-hf","numberposts"=>1,"meta_key"=>"_taabeer_shared","meta_value"=>$type]);$out[$type]=$p[0]->ID;}echo wp_json_encode($out);'))
try:
    php('update_option("blog_public",1);')
    paths = ['/', '/collections/', '/discover-pakistan/', '/about/', '/partnerships/',
             '/contact/', '/privacy-policy/', '/cookie-policy/', '/journal/',
             '/discover-pakistan/regions/']
    paths += ['/discover-pakistan/' + slug + '/' for slug in
              ['contemporary-pakistan', 'pakistan-in-the-making', 'new-voices']]
    paths += ['/discover-pakistan/regions/' + slug + '/' for slug in
              ['punjab', 'sindh', 'balochistan', 'khyber-pakhtunkhwa', 'gilgit-baltistan']]
    paths += ['/collection/the-art-of-' + slug + '/' for slug in
              ['wear', 'living', 'expression', 'adornment', 'leather']]

    def check(path):
        with urlopen('http://localhost:8080' + path, timeout=45) as response:
            html = response.read().decode('utf-8')
            assert response.status == 200, path
        assert len(re.findall(r'<h1(?:\s|>)', html)) == 1, path + ': H1 count'
        assert 'name="description"' in html, path + ': SEO description'
        assert re.search(r'<link[^>]+rel=[\'"]canonical', html), path + ': canonical'
        assert not re.search(r'(?:Deprecated:|Fatal error:|Warning:)', html), path + ': PHP error'
        robots = re.findall(r'<meta[^>]+name=[\'"]robots[\'"][^>]*>', html)
        assert robots and 'noindex' not in ''.join(robots), path + ': public indexing'
        ids = re.findall(r'data-elementor-id="(\d+)"', html)
        headers = [i for i in ids if i == str(shared['header'])]
        footers = [i for i in ids if i == str(shared['footer'])]
        assert headers and footers, path + ': shared Elementor templates'
        return path, headers, footers

    with ThreadPoolExecutor(max_workers=3) as pool:
        rows = list(pool.map(check, paths))
    assert len({tuple(r[1]) for r in rows}) == 1, 'Headers differ between routes'
    assert len({tuple(r[2]) for r in rows}) == 1, 'Footers differ between routes'
    for path, _, _ in rows:
        print('PASS indexable route, headings, SEO and shared templates:', path)
    sitemap = urlopen('http://localhost:8080/page-sitemap.xml', timeout=45).read().decode()
    for path in ['/privacy-policy/', '/cookie-policy/']:
        assert 'http://localhost:8080' + path in sitemap, path + ': missing from sitemap'
    assert '/checkout/' not in sitemap and '/cart/' not in sitemap, 'Draft commerce must stay out of sitemap'
    print('PASS legal pages in sitemap; draft commerce excluded')
finally:
    php('update_option("blog_public",' + json.dumps(original) + ');')
    print('Restored original local staging indexing setting:', original)
