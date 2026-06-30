import http from 'k6/http';
import { sleep, check } from 'k6';

export const options = {
  stages: [
    { duration: '1m', target: 20 },
    { duration: '2m', target: 20 },
    { duration: '1m', target: 50 },
    { duration: '2m', target: 50 },
    { duration: '1m', target: 100 },
    { duration: '2m', target: 100 },
    { duration: '1m', target: 0 },
  ],
  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(95)<1500'],
  },
};

export default function () {
  const home = http.get('https://qimta.com/');
  check(home, {
    'home status is 200': (r) => r.status === 200,
  });

  const products = http.get('https://qimta.com/try');
  check(products, {
    'products status is 200': (r) => r.status === 200,
  });

  const categories = http.get('https://qimta.com/catalog');
  check(categories, {
    'categories status is 200': (r) => r.status === 200,
  });

  sleep(1);
}
