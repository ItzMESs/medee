(function () {
  const form = document.getElementById('article-form');
  if (!form) return;
  const csrf = document.querySelector('meta[name="csrf-token"]').content;

  const quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'Мэдээний агуулгаа энд бичнэ үү...',
    modules: {
      toolbar: {
        container: [
          [{ header: [2, 3, false] }],
          ['bold', 'italic', 'underline', 'strike'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          ['blockquote', 'link', 'image', 'video'],
          [{ align: [] }],
          ['clean'],
        ],
        handlers: { image: uploadImage },
      },
    },
  });

  // Phone photos are often 5-10MB, above many hosts' upload limit, so large JPEG/PNG/WEBP
  // files are shrunk in the browser before sending. GIFs (may be animated) are left alone.
  const MAX_SIDE = 1600;
  async function shrinkImage(file) {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || !window.createImageBitmap) return file;
    try {
      const bmp = await createImageBitmap(file, { imageOrientation: 'from-image' });
      const scale = Math.min(1, MAX_SIDE / Math.max(bmp.width, bmp.height));
      if (scale === 1 && file.size < 1.5 * 1024 * 1024) return file;
      const canvas = document.createElement('canvas');
      canvas.width = Math.round(bmp.width * scale);
      canvas.height = Math.round(bmp.height * scale);
      canvas.getContext('2d').drawImage(bmp, 0, 0, canvas.width, canvas.height);
      const type = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
      const blob = await new Promise((resolve) => canvas.toBlob(resolve, type, 0.85));
      if (!blob || blob.size >= file.size) return file;
      const name = file.name.replace(/\.[^.]+$/, '') + (type === 'image/png' ? '.png' : '.jpg');
      return new File([blob], name, { type, lastModified: Date.now() });
    } catch (e) {
      return file;
    }
  }

  // Inline images are uploaded to the server instead of being embedded as base64.
  function uploadImage() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/jpeg,image/png,image/webp,image/gif';
    input.onchange = async () => {
      if (!input.files[0]) return;
      const file = await shrinkImage(input.files[0]);
      const body = new FormData();
      body.append('image', file);
      const res = await fetch(window.UPLOAD_URL, { method: 'POST', body, headers: { 'x-csrf-token': csrf } });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) return alert(data.error || 'Зураг хуулахад алдаа гарлаа.');
      const range = quill.getSelection(true);
      quill.insertEmbed(range.index, 'image', data.url, 'user');
      quill.setSelection(range.index + 1);
    };
    input.click();
  }

  const editorHtml = () => (quill.getText().trim() || quill.root.querySelector('img,iframe') ? quill.root.innerHTML : '');
  let dirty = false;
  form.addEventListener('submit', () => {
    document.getElementById('content').value = editorHtml();
    dirty = false;
  });

  // Tag input: chips stored as a comma-separated list in a hidden field.
  const tagBox = form.querySelector('[data-tag-input]');
  const tags = { set() {} };
  if (tagBox) {
    const hidden = tagBox.querySelector('input[type=hidden]');
    const box = tagBox.querySelector('.tag-input-box');
    const input = box.querySelector('input');
    let list = [];
    const render = () => {
      box.querySelectorAll('.tag-pill').forEach((el) => el.remove());
      list.forEach((name, i) => {
        const pill = document.createElement('span');
        pill.className = 'tag-pill';
        pill.textContent = '#' + name;
        const x = document.createElement('button');
        x.type = 'button';
        x.textContent = '×';
        x.setAttribute('aria-label', name + ' устгах');
        x.onclick = () => { list.splice(i, 1); sync(); };
        pill.appendChild(x);
        box.insertBefore(pill, input);
      });
    };
    const sync = () => { hidden.value = list.join(', '); render(); form.dispatchEvent(new Event('change')); };
    const add = (raw) => {
      raw.split(/[,;]/).forEach((part) => {
        const name = part.replace(/^#+/, '').replace(/\s+/g, ' ').trim().slice(0, 40);
        if (name && list.length < 10 && !list.some((t) => t.toLowerCase() === name.toLowerCase())) list.push(name);
      });
      input.value = '';
      sync();
    };
    tags.set = (value) => { list = []; add(value || ''); };
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); add(input.value); }
      if (e.key === 'Backspace' && input.value === '' && list.length) { list.pop(); sync(); }
    });
    // Picking a suggestion from the datalist fires "input" without a key press.
    input.addEventListener('input', (e) => { if (!e.inputType || e.inputType === 'insertReplacementText') add(input.value); });
    input.addEventListener('blur', () => { if (input.value.trim()) add(input.value); });
    box.addEventListener('click', (e) => { if (e.target === box) input.focus(); });
    tags.set(hidden.value);
    // Runs after the main submit handler; a typed-but-unconfirmed tag is still saved.
    form.addEventListener('submit', () => {
      if (input.value.trim()) hidden.value = list.concat(input.value.trim()).join(', ');
    });
  }

  // Autosave: every 15 seconds the editor's fields are stored on the server (not published).
  const autosaveUrl = form.dataset.autosaveUrl;
  if (autosaveUrl) {
    const status = form.querySelector('.autosave-status');
    const fields = ['title', 'excerpt', 'category_id', 'tags', 'status', 'published_at'];
    const checks = ['is_featured', 'is_breaking'];
    quill.on('text-change', (delta, old, source) => { if (source === 'user') dirty = true; });
    form.addEventListener('input', () => { dirty = true; });
    form.addEventListener('change', () => { dirty = true; });

    const save = async () => {
      if (!dirty) return;
      dirty = false;
      const body = new FormData();
      body.append('article_id', form.dataset.articleId || '');
      fields.forEach((f) => { const el = form.elements[f]; if (el) body.append(f, el.value); });
      checks.forEach((f) => { const el = form.elements[f]; if (el && el.checked) body.append(f, '1'); });
      body.append('content', editorHtml());
      try {
        const res = await fetch(autosaveUrl, { method: 'POST', body, headers: { 'x-csrf-token': csrf }, credentials: 'same-origin' });
        const data = await res.json();
        if (data.ok) status.textContent = 'Автоматаар хадгалсан ' + data.saved_at;
      } catch (e) {
        dirty = true;
        status.textContent = 'Автомат хадгалалт амжилтгүй — интернэтээ шалгана уу';
      }
    };
    setInterval(save, 15000);
    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'hidden') save(); });
    window.addEventListener('beforeunload', (e) => {
      if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    const offer = document.querySelector('.autosave-offer');
    if (offer) {
      offer.querySelector('[data-autosave-restore]').addEventListener('click', () => {
        const data = JSON.parse(document.getElementById('autosave-data').textContent);
        fields.forEach((f) => { const el = form.elements[f]; if (el && data[f] !== undefined) el.value = data[f]; });
        tags.set(data.tags);
        checks.forEach((f) => { const el = form.elements[f]; if (el) el.checked = data[f] === '1'; });
        quill.clipboard.dangerouslyPasteHTML(data.content || '');
        dirty = true;
        offer.remove();
        status.textContent = 'Ноорог сэргээгдлээ — "Хадгалах" дарж хадгална уу';
      });
      offer.querySelector('[data-autosave-discard]').addEventListener('click', async () => {
        const body = new FormData();
        body.append('article_id', form.dataset.articleId || '');
        await fetch(autosaveUrl + '/discard', { method: 'POST', body, headers: { 'x-csrf-token': csrf }, credentials: 'same-origin' });
        offer.remove();
      });
    }
  }

  const imageInput = document.getElementById('image-input');
  const preview = document.getElementById('image-preview');
  const saveButton = form.querySelector('button[type=submit]');
  if (!imageInput) return;
  imageInput.addEventListener('change', async () => {
    const original = imageInput.files[0];
    if (!original) return;
    preview.src = URL.createObjectURL(original);
    preview.classList.remove('hidden');
    saveButton.disabled = true;
    const file = await shrinkImage(original);
    if (file !== original && window.DataTransfer) {
      const dt = new DataTransfer();
      dt.items.add(file);
      imageInput.files = dt.files;
    }
    saveButton.disabled = false;
  });
})();
