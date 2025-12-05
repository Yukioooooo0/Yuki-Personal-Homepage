# 项目报告 — Yuki-Personal-Homepage

生成时间: 2025-12-06

## 概览
该仓库包含一个简单的静态个人博客，位于 `public/` 目录下。最近我对站点进行了以下改造：

- 将视觉主题切换为 macOS 风格（Light），采用系统字体栈、玻璃卡片、圆角和柔和阴影。
- 为站点添加代码高亮（Highlight.js，CDN 引入）。
- 为全站代码块添加专门样式与“复制”按钮（单击复制到剪贴板）。

本报告列出关键文件、预览与部署说明、以及后续建议。

## 已修改/新增的主要文件
- `public/index.html` — 首页（已更新为 macOS Light 风格，包含终端代码块示例）。
- `public/css/styles.css` — 站点主样式（重写为 macOS 风格，新增 code-toolbar 与 copy 按钮样式）。
- `public/blog/index.html` — 博客列表页（风格与样式调整，示例代码块）。
- `public/posts/sample-post.html` — 示例文章（包含代码块示例）。
- `public/js/code-utils.js` — 新增：为 `pre > code` 添加语言标签和复制按钮，支持剪贴板复制，并在存在 Highlight.js 时触发高亮。
- `public/blog/index.html`、`public/posts/sample-post.html`、`public/index.html` — 已引入 Highlight.js（CDN）与 `code-utils.js`。
- `GITHUB_REPORT.md` — 本文件。

## 功能说明
- 代码高亮：通过 CDN 引入 `highlight.js`，页面加载后会对所有 `pre > code` 执行高亮。
- 复制按钮：每个代码块右上角显示语言标签（若有）和 `复制` 按钮，点击将代码文本复制到系统剪贴板，并显示短暂的“已复制”提示文案。
- 兼容性：复制使用 `navigator.clipboard`（现代浏览器），并包含 `textarea`+`execCommand` 的回退实现。

## 如何本地预览
在 PowerShell 中（仓库根目录）：

```powershell
cd 'c:\Users\Admin\Github\Yuki-Personal-Homepage\public'
# Python 简单服务器
python -m http.server 8000
# 或者使用 live-server（需要 Node）
npx live-server
```
然后在浏览器打开 `http://localhost:8000`。

> 注意：如果你使用 `wrangler pages dev`（Cloudflare Pages 开发工具），请确保端口未冲突并正确安装 wrangler。

## 部署到 GitHub Pages（简要）
1. 把 `public/` 的内容部署到 `gh-pages` 分支，或将仓库设置为 GitHub Pages 并选择 `public/` 目录（若使用 GitHub Actions 构建管道，可写脚本自动复制并推送）。
2. 简单命令（手动）：

```powershell
# 从仓库根目录
git checkout --orphan gh-pages
git --work-tree public add --all
git --work-tree public commit -m "deploy: site"
git push origin gh-pages --force
# 回到 main
git checkout main
```

## 后续增强建议
- 替换或添加 Prism.js（如需更多插件支持：行号、复制按钮、代码折叠等），目前使用的 Highlight.js 已满足基本高亮需求。
- 引入 SF Pro 字体（需授权）或通过 CSS 优雅回退以更贴近 macOS 原生视觉。
- 添加行号或 `复制` 按钮的图标（SVG），以及按钮的 aria/无障碍提示增强。
- 使用静态站点构建工具（Hugo、Jekyll、Eleventy）以便于写 Markdown 并自动生成文章页面、RSS、分页等。

## 变更记录（简短）
- 2025-12-06: 完成 macOS Light 主题、加入 highlight.js 与 code-utils.js、为代码块添加复制功能。

---