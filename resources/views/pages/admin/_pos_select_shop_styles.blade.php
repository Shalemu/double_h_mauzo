<style>
.shop-pick-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    color: #212529;
    transition: border-color .15s, box-shadow .15s, transform .15s;
}
.shop-pick-card:hover,
a:focus-visible .shop-pick-card {
    border-color: #f97316;
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.08);
    transform: translateY(-2px);
}
.shop-pick-icon {
    flex: 0 0 48px;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #f97316;
    background: #fff4ec;
}
.shop-pick-stat {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 8px;
    background: #f6f7f9;
    font-size: 12px;
    color: #4b5563;
}
.shop-pick-stat i { color: #9ca3af; }
.shop-pick-cta {
    font-weight: 600;
    font-size: 14px;
    color: #f97316;
}
.shop-pick-cta i { transition: transform .15s; }
.shop-pick-card:hover .shop-pick-cta i { transform: translateX(3px); }
.shop-pick-card.is-suspended { opacity: .75; }
.shop-pick-card.is-suspended .shop-pick-icon { color: #9ca3af; background: #f3f4f6; }
.min-w-0 { min-width: 0; }

/* Dark theme (navbar toggle sets data-bs-theme="dark") */
[data-bs-theme="dark"] .shop-pick-card { border-color: #374151; color: #e5e7eb; }
[data-bs-theme="dark"] .shop-pick-icon { background: rgba(249,115,22,.15); }
[data-bs-theme="dark"] .shop-pick-stat { background: #1f2937; color: #d1d5db; }
</style>
