import { TransformInterceptor } from './class-transfor.interceptor';
import { ExecutionContext, CallHandler } from '@nestjs/common';
import { firstValueFrom, of } from 'rxjs';

describe('TransformInterceptor', () => {
  let interceptor: TransformInterceptor;

  beforeEach(() => {
    interceptor = new TransformInterceptor();
  });

  it('should be defined', () => {
    expect(interceptor).toBeDefined();
  });

  it('should transform result to plain object', async () => {
    const context = {} as ExecutionContext;
    const next: CallHandler = {
      handle: () => of({ foo: 'bar' }),
    } as CallHandler;

    const result = await firstValueFrom(interceptor.intercept(context, next));

    expect(result).toEqual({ foo: 'bar' });
  });
});
