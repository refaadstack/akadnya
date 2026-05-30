<div class="prose prose-sm max-w-none">
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-blue-900 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Required ZIP Structure
        </h4>
        
        <div class="bg-white rounded border border-gray-200 p-3 font-mono text-xs">
            <div class="space-y-1">
                <div class="text-gray-900">📦 my-template.zip</div>
                <div class="ml-4 text-gray-700">├── 📄 <span class="text-red-600 font-semibold">template.json</span> <span class="text-gray-500">(required)</span></div>
                <div class="ml-4 text-gray-700">├── 📁 <span class="text-red-600 font-semibold">sections/</span> <span class="text-gray-500">(required)</span></div>
                <div class="ml-8 text-gray-600">├── cover.html</div>
                <div class="ml-8 text-gray-600">├── opening.html</div>
                <div class="ml-8 text-gray-600">├── bride-groom.html</div>
                <div class="ml-8 text-gray-600">└── ...</div>
                <div class="ml-4 text-gray-700">├── 📁 <span class="text-red-600 font-semibold">assets/</span> <span class="text-gray-500">(required)</span></div>
                <div class="ml-8 text-gray-600">├── <span class="text-red-600 font-semibold">style.css</span> <span class="text-gray-500">(required)</span></div>
                <div class="ml-8 text-gray-600">├── script.js <span class="text-gray-400">(optional)</span></div>
                <div class="ml-8 text-gray-600">└── images/ <span class="text-gray-400">(optional)</span></div>
                <div class="ml-4 text-gray-700">└── 📁 ornaments/ <span class="text-gray-400">(optional)</span></div>
            </div>
        </div>

        <div class="mt-4 space-y-2">
            <h5 class="text-xs font-semibold text-gray-700 uppercase tracking-wide">template.json Example:</h5>
            <div class="bg-gray-900 text-gray-100 rounded p-3 font-mono text-xs overflow-x-auto">
<pre>{
  "name": "Romantic Wedding",
  "slug": "romantic",
  "version": "1.0.0",
  "thumbnail": "assets/images/thumbnail.jpg",
  "is_free": false,
  "price": 50000,
  "sections": [
    {"file": "cover.html", "label": "Cover"},
    {"file": "opening.html", "label": "Opening"},
    {"file": "bride-groom.html", "label": "Bride & Groom"}
  ]
}</pre>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="bg-green-50 border border-green-200 rounded p-3">
                <h5 class="text-xs font-semibold text-green-800 mb-2 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Required Fields
                </h5>
                <ul class="text-xs text-green-700 space-y-1">
                    <li>✓ name (string)</li>
                    <li>✓ slug (string, unique)</li>
                    <li>✓ sections (array)</li>
                    <li>✓ assets/style.css file</li>
                </ul>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded p-3">
                <h5 class="text-xs font-semibold text-amber-800 mb-2 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    Common Errors
                </h5>
                <ul class="text-xs text-amber-700 space-y-1">
                    <li>• Missing template.json</li>
                    <li>• Invalid JSON syntax</li>
                    <li>• Section files not found</li>
                    <li>• Missing style.css</li>
                </ul>
            </div>
        </div>

        <div class="mt-4 bg-blue-100 border border-blue-300 rounded p-3">
            <p class="text-xs text-blue-800">
                <strong>📚 Need help?</strong> Check the 
                <a href="/docs/templates/TEMPLATE_CREATION_GUIDE.md" target="_blank" class="underline font-semibold">Template Creation Guide</a> 
                for detailed instructions and examples.
            </p>
        </div>
    </div>
</div>
