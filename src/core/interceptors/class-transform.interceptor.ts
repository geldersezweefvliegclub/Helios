import { CallHandler, ExecutionContext, Injectable, NestInterceptor } from '@nestjs/common';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { instanceToPlain } from 'class-transformer';
import { DateTime } from 'luxon';

@Injectable()
export class TransformInterceptor implements NestInterceptor {
  /**
   * Before the response is sent back to the client, the response is transformed to a plain object using class-transformer
   * That way, we can use class-transformer decorators in our classes to transform the response
   * @param context
   * @param next
   */
  intercept(context: ExecutionContext, next: CallHandler): Observable<any> {
    return next.handle().pipe(map(data => instanceToPlain(this.transformDates(data))));
  }

  transformDates(obj: any): any {
    if (obj instanceof Date) {
      const date = DateTime.fromJSDate(obj, { zone: 'Europe/Amsterdam' });
      return date.toFormat('yyyy-MM-dd HH:mm:ss');
    }

    if (typeof obj === 'object') {
      for (const key in obj) {
        obj[key] = this.transformDates(obj[key]);
      }
    }

    return obj;
  }
}
