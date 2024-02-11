import { RequestLoggingMiddleware } from './request-logging.middleware';

describe('RequestLoggingMiddleware', () => {
  it('should be defined', () => {
    expect(new RequestLoggingMiddleware()).toBeDefined();
  });
});
