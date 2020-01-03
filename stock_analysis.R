######imports######
library(keras)
library(dplyr)
library(tidyr)
library(mlbench)
library(magrittr)
library(neuralnet)
###################
#####functions ######
percentage <- function(current,future){
	return ((future-current)/current)
}
classify <- function(current,future){
	if (current < future){
		return (1)
	}else{
	  return (0)
	}
}
minmax <- function(x){
	return ((x-max(x))/(max(x)-min(x)))
}
shift <- function(x, n){
  c(x[-(seq(n))], rep(NA, n))
} 
preprocess <- function(df,a){
  df <- subset(df, select = -c(future))
  df$future <- df$close
  df$close <- shift(df$close,1)
  df$change <- Map(percentage,df$close,df$future)
  X <- array(c(df$change),dim=c(nrow(df),sequence_length,1))
  y <- array(c(df$class),dim=c(nrow(df),1))
  if (a == 1){
    return (X)
  }else{
    return (y)
  }
  
}
##########################3
raw_data <- read.csv("../stocks/^GSPC.csv")
predict_period <- 3
sequence_length <- 20
future <- raw_data$Adj.Close

data <- data.frame(future)
data$close <- shift(data$future,predict_period)
data <- data[predict_period:nrow(data),]

data<-na.omit(data)
data$class <- Map(classify, data$close, data$future)

train_x <- preprocess(data,1)
train_y <- preprocess(data,2)
dim(train_x)<-c(nrow(train_x),20,1)

model <- keras_model_sequential() 

model %>% 
  layer_dense(units = 256, activation = "relu", input_shape = c(20,1)) %>% 
  layer_dense(units = 1, activation = "softmax")

model %>% compile(
  loss = "mse",
  optimizer = "adam",
  metrics = c("accuracy")
)

history <- model %>% fit(
  train_x, train_y, 
  epochs = 30, batch_size = 32
)

