import { HttpExceptionLogger } from './http-exception-logger.filter';
import { HttpAdapterHost } from '@nestjs/core';
import { Test } from '@nestjs/testing';

describe('HTTPExceptionLoggerFilter', () => {
  let mockHttpAdapterHost: HttpAdapterHost;

  beforeEach(async () => {
    const moduleRef = await Test.createTestingModule({
      providers: [
        {
          provide: HttpAdapterHost,
          useValue: {
            httpAdapter: {
              // Mock the methods you need
            },
          },
        },
      ],
    }).compile();

    mockHttpAdapterHost = moduleRef.get<HttpAdapterHost>(HttpAdapterHost);
  });

  it('should be defined', () => {
    expect(new HttpExceptionLogger(mockHttpAdapterHost)).toBeDefined();
  });
});
