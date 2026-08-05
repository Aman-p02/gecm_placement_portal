import os
from PIL import Image

save_dir = os.path.join('assets', 'images', 'team')
os.makedirs(save_dir, exist_ok=True)

img_dir = r'C:\Users\S15\.gemini\antigravity-ide\brain\73a3bd17-a52d-4c68-b837-d4fd560376ff'

# 1. Img 1 (Dr. M M Goyani & Prof. P M Mistri)
im1 = Image.open(os.path.join(img_dir, 'media__1785917137664.png')).convert('RGB')
# Dr. Goyani
im1.crop((202, 252, 282, 348)).save(os.path.join(save_dir, 'goyani.jpg'))
# Prof. Mistri
im1.crop((202, 370, 282, 466)).save(os.path.join(save_dir, 'mistri.jpg'))

# 2. Img 2 (4 members)
im2 = Image.open(os.path.join(img_dir, 'media__1785917205682.png')).convert('RGB')
# A.J. Patel
im2.crop((78, 25, 192, 120)).save(os.path.join(save_dir, 'aj_patel.jpg'))
# M.G. Patel
im2.crop((78, 175, 192, 270)).save(os.path.join(save_dir, 'mg_patel.jpg'))
# J.C. Gamit
im2.crop((78, 332, 192, 428)).save(os.path.join(save_dir, 'jc_gamit.jpg'))
# S.L. Ghanchi
im2.crop((78, 488, 192, 584)).save(os.path.join(save_dir, 'sl_ghanchi.jpg'))

# 3. Img 3 (4 members)
im3 = Image.open(os.path.join(img_dir, 'media__1785917242841.png')).convert('RGB')
# H.K. Sharma
im3.crop((78, 20, 192, 115)).save(os.path.join(save_dir, 'hk_sharma.jpg'))
# A.D. Chaudhari
im3.crop((78, 175, 192, 270)).save(os.path.join(save_dir, 'ad_chaudhari.jpg'))
# S.R. Patel
im3.crop((78, 330, 192, 425)).save(os.path.join(save_dir, 'sr_patel.jpg'))
# N.V. Nagekar
im3.crop((78, 488, 192, 583)).save(os.path.join(save_dir, 'nv_nagekar.jpg'))

# 4. Img 4 (4 members)
im4 = Image.open(os.path.join(img_dir, 'media__1785917253178.png')).convert('RGB')
# M.V. Chauhan
im4.crop((107, 30, 215, 128)).save(os.path.join(save_dir, 'mv_chauhan.jpg'))
# P.V. Patel
im4.crop((107, 175, 215, 272)).save(os.path.join(save_dir, 'pv_patel.jpg'))
# B.A. Brahmbhatt
im4.crop((107, 330, 215, 428)).save(os.path.join(save_dir, 'ba_brahmbhatt.jpg'))
# D.U. Thakkar
im4.crop((107, 486, 215, 584)).save(os.path.join(save_dir, 'du_thakkar.jpg'))

print("Cropped all 14 team photos successfully!")
