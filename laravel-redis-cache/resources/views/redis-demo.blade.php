<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Redis Cache Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">🚀 Laravel Redis Cache Demo</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>📊 Cache Information</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary" onclick="getCacheInfo()">Get Cache Info</button>
                        <button class="btn btn-success" onclick="testCacheConnection()">Test Cache</button>
                        <pre id="cache-info" class="mt-3" style="background: #f8f9fa; padding: 15px; border-radius: 5px;"></pre>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>📦 Products Cache</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-info" onclick="getProducts()">Get All Products</button>
                        <button class="btn btn-warning" onclick="getCacheStats()">Cache Stats</button>
                        <button class="btn btn-danger" onclick="clearCache()">Clear Cache</button>
                        <pre id="products-info" class="mt-3" style="background: #f8f9fa; padding: 15px; border-radius: 5px;"></pre>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>⚡ Performance Test</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Product ID:</label>
                                <input type="number" id="product-id" class="form-control" value="1" min="1" max="10">
                            </div>
                            <div class="col-md-6">
                                <label>&nbsp;</label><br>
                                <button class="btn btn-success" onclick="performanceTest()">Run Performance Test</button>
                            </div>
                        </div>
                        <pre id="performance-result" class="mt-3" style="background: #f8f9fa; padding: 15px; border-radius: 5px;"></pre>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>🏷️ Category Filter Test</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-outline-primary" onclick="getByCategory('Electronics')">Electronics</button>
                        <button class="btn btn-outline-success" onclick="getByCategory('Fashion')">Fashion</button>
                        <button class="btn btn-outline-warning" onclick="getByCategory('Accessories')">Accessories</button>
                        <pre id="category-result" class="mt-3" style="background: #f8f9fa; padding: 15px; border-radius: 5px;"></pre>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info mt-4">
            <h6>💡 Cara Testing:</h6>
            <ol>
                <li>Klik "Get Cache Info" untuk melihat konfigurasi Redis</li>
                <li>Klik "Test Cache" untuk test koneksi Redis</li>
                <li>Klik "Get All Products" untuk load data pertama kali (akan cache otomatis)</li>
                <li>Klik lagi "Get All Products" untuk melihat hasil dari cache (lebih cepat)</li>
                <li>Gunakan "Performance Test" untuk membandingkan DB vs Cache</li>
                <li>Test filter kategori untuk melihat cache per kategori</li>
                <li>Gunakan "Cache Stats" untuk melihat status cache</li>
                <li>Gunakan "Clear Cache" untuk menghapus semua cache</li>
            </ol>
        </div>
    </div>

    <script>
        const baseURL = 'http://localhost:8000';
        
        async function getCacheInfo() {
            try {
                const response = await axios.get(`${baseURL}/demo/cache-info`);
                document.getElementById('cache-info').textContent = JSON.stringify(response.data, null, 2);
            } catch (error) {
                document.getElementById('cache-info').textContent = 'Error: ' + error.message;
            }
        }
        
        async function testCacheConnection() {
            try {
                const response = await axios.get(`${baseURL}/demo/cache-test`);
                document.getElementById('cache-info').textContent = JSON.stringify(response.data, null, 2);
            } catch (error) {
                document.getElementById('cache-info').textContent = 'Error: ' + error.message;
            }
        }
        
        async function getProducts() {
            try {
                const startTime = performance.now();
                const response = await axios.get(`${baseURL}/api/products`);
                const endTime = performance.now();
                
                const result = {
                    request_time_ms: (endTime - startTime).toFixed(2),
                    server_execution_time_ms: response.data.execution_time_ms,
                    total_products: response.data.data.length,
                    data_source: response.data.data_source,
                    cache_key: response.data.cache_key,
                    sample_products: response.data.data.slice(0, 3).map(p => ({
                        id: p.id,
                        name: p.name,
                        price: p.price,
                        category: p.category
                    }))
                };
                
                document.getElementById('products-info').textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                document.getElementById('products-info').textContent = 'Error: ' + error.message;
            }
        }
        
        async function getCacheStats() {
            try {
                const response = await axios.get(`${baseURL}/api/products/cache/stats`);
                document.getElementById('products-info').textContent = JSON.stringify(response.data, null, 2);
            } catch (error) {
                document.getElementById('products-info').textContent = 'Error: ' + error.message;
            }
        }
        
        async function clearCache() {
            try {
                const response = await axios.post(`${baseURL}/api/products/cache/clear`);
                document.getElementById('products-info').textContent = JSON.stringify(response.data, null, 2);
            } catch (error) {
                document.getElementById('products-info').textContent = 'Error: ' + error.message;
            }
        }
        
        async function performanceTest() {
            const productId = document.getElementById('product-id').value;
            try {
                const response = await axios.get(`${baseURL}/api/products/${productId}/performance`);
                document.getElementById('performance-result').textContent = JSON.stringify(response.data, null, 2);
            } catch (error) {
                document.getElementById('performance-result').textContent = 'Error: ' + error.message;
            }
        }
        
        async function getByCategory(category) {
            try {
                const startTime = performance.now();
                const response = await axios.get(`${baseURL}/api/products/category/${category}`);
                const endTime = performance.now();
                
                const result = {
                    category: category,
                    request_time_ms: (endTime - startTime).toFixed(2),
                    server_execution_time_ms: response.data.execution_time_ms,
                    total_products: response.data.total_products,
                    data_source: response.data.data_source,
                    cache_key: response.data.cache_key,
                    products: response.data.data.map(p => ({
                        id: p.id,
                        name: p.name,
                        price: p.price
                    }))
                };
                
                document.getElementById('category-result').textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                document.getElementById('category-result').textContent = 'Error: ' + error.message;
            }
        }
    </script>
</body>
</html>