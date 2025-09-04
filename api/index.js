// هذا الملف للتعامل مع API في Vercel
// يمكنك إضافة مسارات API إضافية هنا

const { createServer } = require('http');
const { parse } = require('url');
const next = require('next');

const dev = process.env.NODE_ENV !== 'production';
const app = next({ dev });
const handle = app.getRequestHandler();

module.exports = async (req, res) => {
  const parsedUrl = parse(req.url, true);
  const { pathname } = parsedUrl;

  // إعادة توجيه API requests إلى PHP
  if (pathname.startsWith('/php/')) {
    // يمكنك إضافة معالجة إضافية هنا إذا لزم الأمر
    return handle(req, res, parsedUrl);
  }

  // معالجة طلبات API الأخرى
  if (pathname === '/api/contact') {
    // معالجة نموذج الاتصال
    if (req.method === 'POST') {
      try {
        const body = JSON.parse(req.body);
        // معالجة البيانات هنا
        res.status(200).json({ success: true, message: 'تم استلام رسالتك بنجاح' });
      } catch (error) {
        res.status(500).json({ success: false, error: 'حدث خطأ أثناء معالجة الطلب' });
      }
      return;
    }
  }

  // إذا لم يتم التعرف على المسار، قم بإرجاع 404
  res.status(404).json({ error: 'Not found' });
};
