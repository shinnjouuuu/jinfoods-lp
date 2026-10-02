# 概略図（p-area-map）の下地＝大分の市町村境界を SVG の <g> に書き出す。
#   python3 map-src/make-land.py            # 原点は既定（北緯33.22699・東経131.59702）
#   python3 map-src/make-land.py 緯度 経度  # 原点を変えるとき（通常は変えない）
# 出力：map-src/area-map-land.svg.html。貼り先は map-src/README.md を参照
import json,math
import os
d=json.load(open(os.path.join(os.path.dirname(os.path.abspath(__file__)),"N03-21_44_210101.json")))
import sys
# 原点＝本社。図の点（地区の代表点）と同じ原点にすること。変えると下地と点がずれる
lat0,lon0=float(sys.argv[1]) if len(sys.argv)>1 else 33.22699, float(sys.argv[2]) if len(sys.argv)>2 else 131.59702
EPS=0.8
kx=math.cos(math.radians(lat0))*111.32*11; ky=110.57*11
def P(x,y): return ((x-lon0)*kx, -(y-lat0)*ky)
def dp(pts,eps):
    if len(pts)<3: return pts
    (x1,y1),(x2,y2)=pts[0],pts[-1]; dx,dy=x2-x1,y2-y1; L=math.hypot(dx,dy)
    if L==0:  # 閉じたリング：始点と終点が同じ。中間点で2つに割って処理する
        k=len(pts)//2
        return dp(pts[:k+1],eps)[:-1]+dp(pts[k:],eps)
    i,m=0,0
    for j in range(1,len(pts)-1):
        x,y=pts[j]; dd=abs(dy*x-dx*y+x2*y1-y2*x1)/L
        if dd>m: i,m=j,dd
    if m>eps: return dp(pts[:i+1],eps)[:-1]+dp(pts[i:],eps)
    return [pts[0],pts[-1]]
paths={}
for f in d["features"]:
    n=f["properties"]["N03_004"]; g=f["geometry"]
    polys=[g["coordinates"]] if g["type"]=="Polygon" else g["coordinates"]
    parts=[]
    for poly in polys:
        r=[P(x,y) for x,y in poly[0]]
        xs=[p[0] for p in r]; ys=[p[1] for p in r]
        if max(xs)<-245 or min(xs)>245 or max(ys)<-135 or min(ys)>230: continue
        s=dp(r,EPS)
        if len(s)<4: continue
        parts.append("M"+"L".join(f"{x:.1f},{y:.1f}" for x,y in s)+"Z")
    if parts: paths[n]="".join(parts)
served={"大分市","別府市","豊後大野市"}
order=sorted(paths,key=lambda n:n in served)  # 受託先の市を後に描く（境界線を上に出す）
g=['<g class="p-area-map__land" aria-hidden="true">']
for n in order:
    cls="p-area-map__city p-area-map__city--served" if n in served else "p-area-map__city"
    g.append(f'  <path class="{cls}" d="{paths[n]}"/><!-- {n} -->')
g.append('</g>')
out=os.path.join(os.path.dirname(os.path.abspath(__file__)),"area-map-land.svg.html")
open(out,"w",encoding="utf-8").write("\n".join(g)+"\n")
print({k:len(v) for k,v in paths.items()}, sum(len(v) for v in paths.values()))
