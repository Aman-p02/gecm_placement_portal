import os
import urllib.request

domains = {
    'flipkart': 'flipkart.com',
    'einfochips': 'einfochips.com',
    'silvertouch': 'silvertouch.com',
    'bacancy': 'bacancytechnology.com',
    'wpweb': 'wpwebinfotech.com',
    'silentinfotech': 'silentinfotech.com',
    'zignuts': 'zignuts.com',
    'hexacoder': 'hexacoder.com',
    'lanetteam': 'lanetteam.com',
    'inovic': 'inoviccrm.com',
    'malayinfotech': 'malayinfotech.com',
    'wamasoftware': 'wamasoftware.com',
    'tectonas': 'tectonas.com',
    'cubein': 'cubein.in',
    'crossshore': 'crossshore.com',
    'iris': 'iris.co.in',
    'alpha-e': 'alpha-e.in',
    'tata': 'tata.com',
    'reliance': 'ril.com',
    'adani': 'adanipower.com',
    'torrentpower': 'torrentpower.com',
    'polycab': 'polycab.com',
    'inoxwind': 'inoxwind.com',
    'exide': 'exideindustries.com',
    'involt': 'involt.in',
    'gelco': 'gelcoelectronics.com',
    'bmw': 'bmw.in',
    'alstom': 'alstom.com',
    'khs': 'khs.com',
    'jbm': 'jbmgroup.com',
    'sltl': 'sltl.com',
    'masibus': 'masibus.com',
    'mehta': 'mehtaindia.com',
    'multispan': 'multispanindia.com',
    'marutair': 'marutair.com',
    'arcedges': 'arcedges.com',
    'oswal': 'oswalvalves.com',
    'partechno': 'partechnoheat.com',
    'dbc': 'dbc.co.in',
    'isro': 'isro.gov.in',
    'gsi': 'gsi.gov.in',
    'stembotix': 'stembotix.com',
    'orena': 'orena.solutions',
    'monarch': 'monarchinnovation.com',
    'atigo': 'atigo.in',
    'panacea': 'panacea.co.in',
    'surekha': 'surekhatech.com',
    'jpresearch': 'jpresearch.in',
    'casepoint': 'casepoint.com'
}

save_dir = os.path.join('assets', 'images', 'recruiters')
os.makedirs(save_dir, exist_ok=True)

headers = {'User-Agent': 'Mozilla/5.0'}

for key, dom in domains.items():
    filepath = os.path.join(save_dir, f"{key}.png")
    # Try Clearbit first
    urls = [
        f"https://logo.clearbit.com/{dom}",
        f"https://www.google.com/s2/favicons?domain={dom}&sz=128"
    ]
    downloaded = False
    for url in urls:
        try:
            req = urllib.request.Request(url, headers=headers)
            with urllib.request.urlopen(req, timeout=5) as response:
                if response.status == 200:
                    data = response.read()
                    if len(data) > 200:
                        with open(filepath, 'wb') as f:
                            f.write(data)
                        print(f"Downloaded {key} from {url}")
                        downloaded = True
                        break
        except Exception as e:
            continue
    if not downloaded:
        print(f"Failed to download {key}")
