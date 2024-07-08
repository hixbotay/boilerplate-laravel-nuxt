export const API_METHOD = {
  GET: "get",
  POST: "post",
  PUT: "put",
  DELETE: "delete",
  PATCH: "patch"
};

export const API_CODE = {
  Succeed: 200,
  DeleteSucceed: 202,
  BadRequestError: 400,
  NotFoundError: 404,
  UnauthorizedError: 401,
  Forbidden: 403,
  InternalServerError: 500,
  ApiGatewayTimeoutError: 504,
};

export const PAGING = {
  PerPage: 20
}

export const URL_API = {
  baseApiUrl: `${process.env.baseAPIUrl}`,
}

export const PAY_STATUS={
  waitting: {display:'Đang chờ duyệt',value:1},
  approve: {display:'Đã duyệt',value:2},
  cancel: {display:'Hủy bỏ',value:3}
}

export const PRODUCT_ITEM_STATUS={
  active: {display:'Hoạt động',value:1},
  deactive: {display:'Ẩn',value:2},
  selled: {display:'Đã bán',value:3}
}
